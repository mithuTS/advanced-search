<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use TCG\Voyager\Database\Schema\SchemaManager;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBreadController;

class VoyagerBaseController extends VoyagerBreadController
{
    /**
     * Advanced search flag
     * @var bool
     */
    protected $advSearch = false;
    private $pdfExport = false;


    /**
     * Method to change the falg value
     * @param bool $true
     */
    public function setAdvSearch( $true = true ) {
        $this->advSearch = $true;
    }

    public function setPdfExport( $true = true ) {
        $this->pdfExport = $true;
    }


    /**
     * Check if the module is
     * applicable for advanced search
     * @return bool
     */
    public function isSearchable() {
        return $this->advSearch;
    }

    public function isPdfExportable() {
        return $this->pdfExport;
    }


    /**
     * Build search query by
     * search conditions provided
     * @param $query
     * @return mixed
     */
    public function makeSearchQuery( $query ) {

        if( isset( $_GET['search_data'] ) && $_GET['search_data'] ) {
            $search_data = json_decode( base64_decode( $_GET['search_data'] ),true );

            if( is_array( $search_data ) && !empty( $search_data ) ) {

                foreach ( $search_data as $k => $group ) {

                    if( isset( $group['search_fields'] ) && is_array( $group['search_fields'] ) && !empty( $group['search_fields'] ) ) {
                        $group_fields = $group['search_fields'];

                        if( $group['join'] == 'and' ) {
                            $query->where(function($query) use ($group_fields) {
                                return $this->makeQueryForFields( $query, $group_fields);
                            });
                        } else {
                            $query->orWhere(function($query) use ($group_fields) {
                                return $this->makeQueryForFields( $query, $group_fields);
                            });
                        }

                    }
                }
            }
        }
        return $query;
    }


    /**
     * Make query for fields
     * @param $query
     * @param $group_fields
     * @return mixed
     */
    public function makeQueryForFields( $query, $group_fields) {

        foreach ( $group_fields as $k => $each_field ) {

            if( !$each_field['value'] ) continue;

            $matching_key = $each_field['key'];
            $matching_operator = $each_field['operator'];
            $matching_value = $each_field['operator'] == 'LIKE'
            || $each_field['operator'] == 'NOT LIKE' ? '%'.$each_field['value'].'%' : $each_field['value'];

            if( isset( $each_field['join'] ) && $each_field['join'] == 'or' ) {
                $query->orWhere( $matching_key, $matching_operator, $matching_value );
            } else {
                $query->where( $matching_key, $matching_operator, $matching_value );
            }

        }

        return $query;
    }


    /**
     * Index page
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request) {
        $advSearch = $this->isSearchable();
        $pdfExport = $this->isPdfExportable();

        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug($request);

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('browse', app($dataType->model_name));

        $getter = $dataType->server_side ? 'paginate' : 'get';

        $search = (object) ['value' => $request->get('s'), 'key' => $request->get('key'), 'filter' => $request->get('filter')];
        $searchable = $dataType->server_side ? array_keys(SchemaManager::describeTable(app($dataType->model_name)->getTable())->toArray()) : '';
        $orderBy = $request->get('order_by');
        $sortOrder = $request->get('sort_order', null);

        // Next Get or Paginate the actual content from the MODEL that corresponds to the slug DataType
        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);
            $query = $model::select('*');

            $relationships = $this->getRelationships($dataType);

            // If a column has a relationship associated with it, we do not want to show that field
            $this->removeRelationshipField($dataType, 'browse');

            if ($search->value && $search->key && $search->filter) {
                $search_filter = ($search->filter == 'equals') ? '=' : 'LIKE';
                $search_value = ($search->filter == 'equals') ? $search->value : '%'.$search->value.'%';
                $query->where($search->key, $search_filter, $search_value);
            }

            $query = $this->makeSearchQuery($query);

            if ($orderBy && in_array($orderBy, $dataType->fields())) {
                $querySortOrder = (!empty($sortOrder)) ? $sortOrder : 'DESC';
                $dataTypeContent = call_user_func([
                    $query->with($relationships)->orderBy($orderBy, $querySortOrder),
                    $getter,
                ]);
            } elseif ($model->timestamps) {
                $dataTypeContent = call_user_func([$query->latest($model::CREATED_AT), $getter]);
            } else {
                $dataTypeContent = call_user_func([$query->with($relationships)->orderBy($model->getKeyName(), 'DESC'), $getter]);
            }

            // Replace relationships' keys for labels and create READ links if a slug is provided.
            $dataTypeContent = $this->resolveRelations($dataTypeContent, $dataType);
        } else {
            // If Model doesn't exist, get data from table name
            $dataTypeContent = call_user_func([DB::table($dataType->name), $getter]);
            $model = false;
        }

        // Check if BREAD is Translatable
        if (($isModelTranslatable = is_bread_translatable($model))) {
            $dataTypeContent->load('translations');
        }

        // Check if server side pagination is enabled
        $isServerSide = isset($dataType->server_side) && $dataType->server_side;

        $view = 'voyager::bread.browse';

        if (view()->exists("voyager::$slug.browse")) {
            $view = "voyager::$slug.browse";
        }
//dd($dataTypeContent->toArray());
        return Voyager::view($view, compact(
            'dataType',
            'dataTypeContent',
            'isModelTranslatable',
            'search',
            'advSearch',
            'pdfExport',
            'orderBy',
            'sortOrder',
            'searchable',
            'isServerSide'
        ));
    }

}
