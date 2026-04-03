<div class="chartHeading mt-3 bg-white  d-flex justify-content-between p-20 rounded-top">
    <h3 class="f-21 f-w-500 mb-0">@lang('modules.zone.dragAndDrop')</h3>
</div>

<div id="dragRoot" class="pt-3 rounded-bottom">
    @foreach ($zones as $zone)
        <ul>
            <li value="{{$zone->id}}" >
                <span id="{{$zone->id}}" class="node-cpe">&rightarrow; {{ $zone->team_name }}</span>
                @if ($zone->childs)
                    @include('zones-hierarchy.manage_hierarchy', [
                        'childs' => $zone->childs,'parent_id' => $zone->id
                    ])
                @endif
            </li>
        </ul>
    @endforeach
    <ul id="pre-state"></ul>
    <ul id="drophere" ondragstart="return false;" ondrop="return false;">
        <li><span id="NewNode" class="node-cpe">@lang('app.newHierarchy')</span></span></li>
    </ul>
</div>
