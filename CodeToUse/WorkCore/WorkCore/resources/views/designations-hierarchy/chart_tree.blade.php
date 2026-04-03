<div class="chartHeading mt-3 bg-white  d-flex justify-content-between p-20 rounded-top">
    <h3 class="f-21 f-w-500 mb-0">@lang('modules.zone.dragAndDrop')</h3>
</div>

<div id="dragRoot" >
    @foreach ($roles as $role)
            <ul>
                <li value="{{$role->id}}" >
                        <span id="{{$role->id}}" class="node-cpe">&rightarrow; {{ $role->name }}</span>
                    @if ($role->childs)
                        @include('roles-hierarchy.manage_hierarchy', [
                            'childs' => $role->childs,'parent_id' => $role->id
                        ])
                    @endif
                </li>
            </ul>
    @endforeach
    <ul id="pre-state"></ul>
    <ul id="drophere" ondragstart="return false;" ondrop="return false;">
        <li ><span id="NewNode" class="node-cpe">@lang('app.newHierarchy')</span></span></li>
    </ul>
</div>
