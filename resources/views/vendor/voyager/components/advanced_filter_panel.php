<template id="advanced_filter_panel">
    <div class="advanced_filter_panel">
        <form method="get">
            <input type="hidden" name="search_data" v-model="search_data">
            <div v-for="( group, group_key ) in search" class="search_group container-fluid">
                <div class="">
                    <div class="row">
                        <div class="group_panel">
                            <div class="group_remover">
                                <a href="javascript:" class="btn btn-danger pull-right btn-xs" @click="removeSearchGroup( group_key )"><i class="glyphicon glyphicon-remove"></i></a>
                            </div>
                        </div>
                        <template v-if="group_key >0">
                            <div class="group_joiner">
                                <div class="col-sm-2">
                                    <select v-model="group.join" class="form-control">
                                        <option value="and">And</option>
                                        <option value="or">OR</option>
                                    </select>
                                </div>
                            </div>
                        </template>
                        <div class="col-sm-12">
                            <div class="container-fluid">
                                <div class="row">
                                    <div v-for="( s, k ) in group.search_fields" class="search_field col-sm-10">
                                        <div class="row">
                                            <div class="col-sm-3" v-if="k >0">
                                                <select v-model="s.join" class="form-control">
                                                    <option value="and">And</option>
                                                    <option value="or">OR</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-3">
                                                <!--s.key-->
                                                <select v-model="s.key" class="form-control" @change="setValueField(s.key)">
                                                    <option :value="field_array.field" v-for="( field_array, index ) in dataType.browse_rows">{{ field_array.display_name }}</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-2">
                                                <select v-model="s.operator" class="form-control">
                                                    <option :value="operator" v-for="( label, operator ) in operators">{{ label }}</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-3">
                                                <template v-if="field_type_list[s.key] == 'radio_btn'">
                                                    <select v-model="s.value" :class="generateFieldClass(s.key)" class="form-control">
                                                        <option :value="value" v-for="(label, value) in JSON.parse(selected_row[s.key].details).options">{{ label }}</option>
                                                    </select>
                                                </template>
                                                <template v-else-if="field_type_list[s.key] == 'checkbox'">
                                                    <select v-model="s.value" :class="generateFieldClass(s.key)" class="form-control">
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </template>
                                                <template v-else-if="field_type_list[s.key] == 'select_dropdown'">
                                                    <select v-model="s.value" :class="generateFieldClass(s.key)" class="form-control">
                                                        <option :value="value" v-for="(label, value) in JSON.parse(selected_row[s.key].details).options">{{ label }}</option>
                                                    </select>
                                                </template>
                                                <template v-else-if="field_type_list[s.key] == 'select_multiple'">
                                                    <select v-model="s.value" multiple :class="generateFieldClass(s.key)" class="form-control">
                                                        <option :value="value" v-for="(label, value) in JSON.parse(selected_row[s.key].details).options">{{ label }}</option>
                                                    </select>
                                                </template>
                                                <template v-else>
                                                    <input type="text" :id="generateFieldId(s.key)" v-model="s.value" class="form-control" :class="generateFieldClass(s.key)">
                                                </template>
                                            </div>
                                            <div>
                                                <a href="javascript:" class="btn btn-danger field_row_remove_btn pull-right" @click="removeSearchRow( group_key, k )"><i class="glyphicon glyphicon-minus-sign"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pull-left">
                                        <a href="javascript:" class="btn btn-success field_row_add_btn" @click="addSearchRow( group_key )"><i class="voyager-plus"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form_parent_panel">
                <a class="btn btn-success" href="javascript:" @click="addSearchGroup()"><i class="voyager-plus"></i> Add Group</a>
                <button class="btn btn-info pull-right"><i class="voyager-search"></i> Search</button>
            </div>

        </form>
    </div>
</template>
<?php $search_data = array();

if( isset( $_GET['search_data'] ) && $_GET['search_data'] ) {
    $search_data = json_decode( base64_decode( $_GET['search_data'] ),true );
    !is_array( $search_data ) ? $search_data = array() : '' ;
}

$search_data = json_encode( $search_data ); ?>

<script>
    $field_list = JSON.parse('<?php echo ( json_encode($field_list) );?>');
    $field_type_list = JSON.parse('<?php echo ( json_encode($field_type_list) ); ?>');
    $dataType = JSON.parse(atob('<?php echo stripslashes( base64_encode(json_encode($dataType)) ) ; ?>'));
    $dataTypeContent = JSON.parse(atob('<?php echo stripslashes( base64_encode(json_encode($dataTypeContent)) ) ; ?>'));
</script>
<script>
    Vue.component( 'advanced_filter_panel', {
        template: '#advanced_filter_panel',
        data: function () {
            return {
                testName: '',
                prev_search_data: JSON.parse( '<?php echo $search_data; ?>' ),
                dataType : $dataType,
                dataTypeContent : $dataTypeContent,
                selected_row: {},
                operators: {
                    '=' : 'Equals',
                    '!=' : 'Not Equals',
                    '>' : 'Greater Than',
                    '<' : 'Lower Than',
                    'LIKE' : 'Contains',
                    'NOT LIKE' : 'Not Contains'
                },
                searchable_fields: $field_list,
                field_type_list: $field_type_list,
                search : [
                    {
                        join: 'and',
                        search_fields : [
                            {
                                key: 'first_name',
                                operator: '=',
                                value: ''
                            }
                        ]
                    }
                ]
            }
        },
        computed: {
            search_data: function () {
                return btoa(JSON.stringify(this.search));
            }
        },
        methods: {
            generateFieldId: function (field_name) {
              var id = 'field-' + this.field_type_list[field_name] + (new Date()).getTime();
              return id;
            },
            setValueField: function ( field_name ) {
              var _this = this;
              var item = this.dataType.browse_rows.filter(function (item) {
                  if( item.field == field_name ) {
                      return item;
                  }
              });

              if( item.length ) {
                  this.selected_row[field_name] = item[0];
              } else {
                  this.selected_row[field_name] = [];
              }

            },
            generateFieldClass: function ( field_name ) {
                this.resetElements();
                return 'field-' + this.field_type_list[field_name];
            },
            addSearchRow: function ( group_key ) {
                this.insertFirstElement( group_key );
            },
            removeSearchRow: function ( group_key, k ) {
                Vue.delete( this.search[group_key].search_fields, k );
            },
            addSearchGroup: function () {
                this.search.push({
                    join: 'and',
                    search_fields: []
                });
            },
            removeSearchGroup: function ( group_key ) {
                Vue.delete( this.search, group_key );
            },
            insertFirstElement: function ( group_key ) {
                //alert('qwewqeqwe');
                var element = {
                    join: 'and',
                    key: Object.keys(this.searchable_fields)[0],
                    operator: Object.keys(this.operators)[0],
                    value: ''
                };

                if( typeof group_key == 'undefined' ) {
                    this.removeSearchGroup();
                    group_key = 0;
                }
                this.search[group_key].search_fields.push(element);
            },
            resetElements: function ( type, id ) {
                $('.field-select_multiple,.field-select_dropdown, .field-radio_btn, .field-select_dropdown,.field-checkbox').each(function () {
                    if( $(this).next('.select2-container').length ) {
                        $(this).next('.select2-container').remove();
                    }
                });
                $('.field-select_multiple,.field-select_dropdown, .field-radio_btn, .field-select_dropdown,.field-checkbox').next('.select2-container').remove();
                $('.field-select_multiple,.field-select_dropdown, .field-radio_btn, .field-select_dropdown,.field-checkbox').hide();
                setTimeout(
                    function () {
                        $('.field-timestamp').datetimepicker();
                        $('.field-select_multiple,.field-select_dropdown, .field-radio_btn, .field-select_dropdown,.field-checkbox')
                            .select2({
                            minimumResultsForSearch: Infinity
                        })
                            .show();
                    }
                ,300 );
            }
        },
        created: function () {
            if( this.prev_search_data.length ) {
                this.search = this.prev_search_data;
            }
            if( !this.search.length ){
                this.insertFirstElement();
            }
        },
        mounted: function () {
            this.resetElements();
        }
    });
</script>