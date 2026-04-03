<div class="modal-body">
    @foreach($response as $que => $ans)
        <div class="col-12 text-xs">
            <b>{{$que}}</b> <br>
            <p>{{$ans}}</p>
        </div>
    @endforeach
</div>
