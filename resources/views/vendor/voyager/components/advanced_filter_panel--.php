<template id="advanced_filter_panel">
    <div class="advanced_filter_panel">
        <form method="get">
            <input type="hidden" name="search_data" v-model="search_data">

            <div v-for="( group, group_key ) in search" class="search_group">
                <div class="">
                    <div class="row">
                        <template v-if="group_key >0">
                            <div class="col-sm-2">
                                <select v-model="group.join" class="form-control">
                                    <option value="and">And</option>
                                    <option value="or">OR</option>
                                </select>
                            </div>
                        </template>
                        <div class="col-sm-10">
                            <div v-for="( s, k ) in group.search_fields" class="search_field" id="search-input2">
                                <div class="row">
                                    <template v-if="k >0">
                                        <div class="col-sm-2">
                                            <select v-model="s.join" class="form-control">
                                                <option value="and">And</option>
                                                <option value="or">OR</option>
                                            </select>
                                        </div>
                                    </template>
                                    <select id="search_key" v-model="s.key" class="form-control">
                                        <option :value="field_name" v-for="( field_label, field_name ) in searchable_fields">{{ field_label }}</option>
                                    </select>
                                    <select id="filter" v-model="s.operator" class="form-control">
                                        <option :value="operator" v-for="( label, operator ) in operators">{{ label }}</option>
                                    </select>
                                    <div class="input-group col-md-12">
                                        <input :type="field_type_list[s.key]" v-model="s.value" class="form-control" :class="generate_field_class(s.key)">
                                        <span class="input-group-btn"><button type="submit" class="btn btn-info btn-lg" @click="remove_search_row( group_key, k )"><i class="glyphicon glyphicon-minus-sign"></i></button></span>
                                    </div>
                                </div>
                            </div>
                            <div class="group_panel">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <a href="javascript:" class="btn btn-success" @click="add_search_row( group_key )"><i class="voyager-plus"></i> Add Search Field</a>
                                        <a href="javascript:" class="btn btn-danger" @click="remove_search_group( group_key )"><i class="glyphicon glyphicon-minus-sign"></i> Remove Group</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form_parent_panel">
                <a class="btn btn-success" href="javascript:" @click="add_search_group()"><i class="voyager-plus"></i> Add Search Group</a>
                <button class="btn btn-info"><i class="voyager-search"></i> Search</button>
            </div>

        </form>
    </div>
</template>
<?php
$search_data = array();
if( isset( $_GET['search_data'] ) && $_GET['search_data'] ) {
    $search_data = json_decode( base64_decode( $_GET['search_data'] ),true );
    !is_array( $search_data ) ? $search_data = array() : '' ;
}
$search_data = json_encode( $search_data );
?>
<script>
    $field_list = JSON.parse('<?php echo ( json_encode($field_list) );?>');
    $field_type_list = JSON.parse('<?php echo ( json_encode($field_type_list) ); ?>');
</script>
<script>
    Vue.component( 'advanced_filter_panel', {
        template: '#advanced_filter_panel',
        data: function () {
            return {
                prev_search_data: JSON.parse( '<?php echo $search_data; ?>' ),
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
            generate_field_class: function ( field_name ) {
                return 'field-' + this.field_type_list[field_name];
            },
            add_search_row: function ( group_key ) {
                /*this.search[group_key].search_fields.push({
                    join: 'and',
                    key: 'last_name',
                    operator: '=',
                    value: 'm'
                });*/
                this.insert_first_element( group_key );
            },
            remove_search_row: function ( group_key, k ) {
                Vue.delete( this.search[group_key].search_fields, k );
            },
            add_search_group: function () {
                this.search.push({
                    join: 'and',
                    search_fields: []
                });
            },
            remove_search_group: function ( group_key ) {
                Vue.delete( this.search, group_key );
            },
            insert_first_element: function ( group_key ) {
                var element = {
                    join: 'and',
                    key: Object.keys(this.searchable_fields)[0],
                    operator: Object.keys(this.operators)[0],
                    value: ''
                };

                if( typeof group_key == 'undefined' ) {
                    console.log('qqqqqqq');
                    this.add_search_group();
                    group_key = 0;
                }
                this.search[group_key].search_fields.push(element);
            }
        },
        created: function () {
            console.log( JSON.parse( JSON.stringify( this.prev_search_data ) ) );
            this.search = this.prev_search_data;
            if( !this.search.length ){
                this.insert_first_element();
            }
            $('#search-input2 select').select2({
                minimumResultsForSearch: Infinity
            });
        }
    });
</script>