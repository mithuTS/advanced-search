<template id="inline_add_edit">
<div>
    <form role="form"
          class="form-edit-add"
          action="@if(isset($dataTypeContent->id)){{ route('voyager.'.$dataType->slug.'.update', $dataTypeContent->id) }}@else{{ route('voyager.'.$dataType->slug.'.store') }}@endif"
          method="POST" enctype="multipart/form-data">
        <table>
            <tr>
                @if (count($errors) > 0)
                @endif
                <!-- Adding / Editing -->
                @php
                $dataTypeRows = $dataType->{(isset($dataTypeContent->id) ? 'editRows' : 'addRows' )};

                @endphp
                <td></td>
                @foreach($dataTypeRows as $row)

                <!-- PUT Method if we are editing -->
                @if(isset($dataTypeContent->id))
                {{ method_field("PUT") }}
                @endif
                <!-- CSRF TOKEN -->
                {{ csrf_field() }}
                <!-- GET THE DISPLAY OPTIONS -->
                @php
                $options = json_decode($row->details);
                $display_options = isset($options->display) ? $options->display : NULL;
                @endphp
                @if ($options && isset($options->formfields_custom))
                <td>
                    @include('voyager::formfields.custom.' . $options->formfields_custom)
                </td>
                @else
                <td class="form-group @if($row->type == 'hidden') hidden @endif @if(isset($display_options->width)){{ 'col-md-' . $display_options->width }}@else{{ '' }}@endif" @if(isset($display_options->id)){{ "id=$display_options->id" }}@endif>
                    {{ $row->slugify }}
                    <label for="name">{{ $row->display_name }}</label>
                    @include('voyager::multilingual.input-hidden-bread-edit-add')
                    @if($row->type == 'relationship')
                    @include('voyager::formfields.relationship')
                    @else
                    {!! app('voyager')->formField($row, $dataType, $dataTypeContent) !!}
                    @endif

                    @foreach (app('voyager')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                    {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                    @endforeach
                </td>
                @endif
                @endforeach
                <td><button type="submit" data-update-id="{{ $dataTypeContent->id }}" class="update-content btn btn-primary save">{{ __('voyager.generic.save') }}</button></td>
                {{--<div class="panel-footer">
                    <button type="submit" class="btn btn-primary save">{{ __('voyager.generic.save') }}</button>
                </div>--}}
            </tr>
        </table>

    </form>
</div>
</template>
<script>
    Vue.component( 'inline_add_edit', {
        template: '#inline_add_edit',
        data: function () {
            return {
                $dataTypeContent : $dataTypeContent,
                $dataType : $dataType
            }
        }
    });
</script>
<!-- End Delete File Modal -->