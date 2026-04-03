<style>
    .rating-stars ul {
        list-style-type: none;
        padding: 0;
        -moz-user-select: none;
        -webkit-user-select: none;
    }

    .rating-stars ul>li.star {
        display: inline-block;
        margin: 1px;
    }

    /* Idle State of the stars */
    .rating-stars ul>li.star>i.fa {
        /* font-size: 1.6em; */
        /* Change the size of the stars */
        color: #ccc;
        /* Color on idle state */
    }

    /* Hover state of the stars */
    .rating-stars ul>li.star.hover>i.fa {
        color: var(--header_color);
    }

    /* Selected state of the stars */
    .rating-stars ul>li.star.selected>i.fa {
        color: var(--header_color);
    }

    .selected {
        color: var(--header_color);
    }

</style>

<!-- ROW START -->
<div class="row py-3 py-lg-5 py-md-5">

    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">

        @php
            $memberIds = $site->members->pluck('user_id')->toArray();
        @endphp

        @if (
            $editRatingPermission == 'all'
            || ($editRatingPermission == 'added' && $site->rating && ($site->rating->added_by == user()->id || $site->rating->added_by == $userId))
            || ($editRatingPermission == 'owned' && (in_array(user()->id, $memberIds) || $site->client_id == $userId))
            || ($editRatingPermission == 'both' && (in_array(user()->id, $memberIds) || $site->client_id == $userId || ($site->rating && ($site->rating->added_by == user()->id || $site->rating->added_by == $userId))))
            || in_array('customer', user_roles())
            )

            <x-form id="save-site-rating-form" @class(['d-none' => (!is_null($site->rating) || ($addRatingPermission == 'none') && is_null($site->rating))])>
                <div class="add-customer rounded bg-white">
                    <div class="row p-20">

                        <div class="col-md-12">
                            <x-forms.label :fieldLabel="__('app.menu.projectRating')" fieldId="site-rating" />
                            <div class="rating-stars">
                                <ul id="stars">
                                    <li class="star @if (!is_null($site->rating) &&
                                        $site->rating->rating >= 1) selected @endif"
                                        title="Poor" data-value="1">
                                        <i class="fa fa-star f-18"></i>
                                    </li>
                                    <li class="star @if (!is_null($site->rating) &&
                                        $site->rating->rating >= 2) selected @endif"
                                        title="Fair" data-value="2">
                                        <i class="fa fa-star f-18"></i>
                                    </li>
                                    <li class="star @if (!is_null($site->rating) &&
                                        $site->rating->rating >= 3) selected @endif"
                                        title="Good" data-value="3">
                                        <i class="fa fa-star f-18"></i>
                                    </li>
                                    <li class="star @if (!is_null($site->rating) &&
                                        $site->rating->rating >= 4) selected @endif"
                                        title="Excellent" data-value="4">
                                        <i class="fa fa-star f-18"></i>
                                    </li>
                                    <li class="star @if (!is_null($site->rating) &&
                                        $site->rating->rating >= 5) selected @endif"
                                        title="WOW!!!" data-value="5">
                                        <i class="fa fa-star f-18"></i>
                                    </li>
                                </ul>
                            </div>

                            <div class="form-group">
                                {{-- raing id here --}}
                                    <input type="hidden" name="rating" id="ratingID" @if (!is_null($site->rating)) value="{{ $site->rating->id }}" @endif>
                            </div>

                        </div>

                        <div class="col-md-12 mt-4">
                            <x-forms.textarea fieldId="comment" fieldName="comment" :fieldLabel="__('app.comment')"
                                :fieldValue="(!is_null($site->rating) ? $site->rating->comment : '')" />
                        </div>
                    </div>

                    <!-- CANCEL SAVE SEND START -->
                    @if (is_null($site->deleted_at))
                        <div class="px-lg-4 px-md-4 px-3 py-3 c-inv-btns">
                            <div class="d-flex">
                                <x-forms.button-primary class="save-form" icon="check">@lang('app.save')
                                </x-forms.button-primary>
                            </div>
                        </div>
                    @endif
                    <!-- CANCEL SAVE SEND END -->

                </div>
            </x-form>
        @endif

        <div class="add-customer rounded bg-white">
            <div class="row p-20">

                @if (!is_null($site->rating))
                    @if (
                        $viewRatingPermission == 'all'
                        || ($viewRatingPermission == 'added' && $site->rating && ($site->rating->added_by == user()->id || $site->rating->added_by == $userId))
                        || ($viewRatingPermission == 'owned' && (in_array(user()->id, $memberIds) || $site->client_id == $userId))
                        || ($viewRatingPermission == 'both' && (in_array(user()->id, $memberIds) || $site->client_id == $userId || ($site->rating && ($site->rating->added_by == user()->id || $site->rating->added_by == $userId))))
                        )

                        <div class="col-md-12 mt-1 rating-detail">
                            <div class="rating-stars">
                                <ul>
                                    <li class="star @if ($site->rating->rating >= 1) selected @endif" title="Poor" data-value="1">
                                        <i class="fa fa-star f-18"></i>
                                    </li>
                                    <li class="star @if ($site->rating->rating >= 2) selected @endif" title="Fair" data-value="2">
                                        <i class="fa fa-star f-18"></i>
                                    </li>
                                    <li class="star @if ($site->rating->rating >= 3) selected @endif" title="Good" data-value="3">
                                        <i class="fa fa-star f-18"></i>
                                    </li>
                                    <li class="star @if ($site->rating->rating >= 4) selected @endif" title="Excellent" data-value="4">
                                        <i class="fa fa-star f-18"></i>
                                    </li>
                                    <li class="star @if ($site->rating->rating >= 5) selected @endif" title="WOW!!!" data-value="5">
                                        <i class="fa fa-star f-18"></i>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-12 mt-4 rating-detail">
                            <blockquote class="blockquote">
                                <p class="mb-0 f-16">{{ nl2br($site->rating->comment) }}</p>
                                <footer class="blockquote-footer f-14">{{ $site->customer->name }}, <em class="f-11">{{ $site->rating->created_at->diffForHumans() }}</em></footer>
                            </blockquote>

                            @if (
                                is_null($site->deleted_at) &&
                                $editRatingPermission == 'all'
                                || ($editRatingPermission == 'added' && $site->rating && ($site->rating->added_by == user()->id || $site->rating->added_by == $userId))
                                || ($editRatingPermission == 'owned' && (in_array(user()->id, $memberIds) || $site->client_id == $userId))
                                || ($editRatingPermission == 'both' && (in_array(user()->id, $memberIds) || $site->client_id == $userId || ($site->rating && ($site->rating->added_by == user()->id || $site->rating->added_by == $userId))))
                                || in_array('customer', user_roles())
                                )
                                <a href="javascript:;" class="text-darkest-grey edit-rating"><u>@lang('app.edit')</u></a>
                            @endif

                            @if (
                                is_null($site->deleted_at) &&
                                $deleteRatingPermission == 'all'
                                || ($deleteRatingPermission == 'added' && $site->rating && ($site->rating->added_by == user()->id || $site->rating->added_by == $userId))
                                || ($deleteRatingPermission == 'owned' && (in_array(user()->id, $memberIds) || $site->client_id == $userId))
                                || ($deleteRatingPermission == 'both' && (in_array(user()->id, $memberIds) || $site->client_id == $userId || ($site->rating && ($site->rating->added_by == user()->id || $site->rating->added_by == $userId))))
                                || in_array('customer', user_roles())
                                )
                                <a href="javascript:;" data-rating-id="{{  $site->rating->id }}" class="text-darkest-grey delete-rating ml-2"><u>@lang('app.delete')</u></a>
                            @endif

                        </div>

                    @else
                        <x-cards.no-record icon="star" :message="__('modules.sites.noRatingAvailable')" />
                    @endif
                @else
                    <x-cards.no-record icon="star" :message="__('modules.sites.noRatingAvailable')" />
                @endif

            </div>
        </div>

    </div>
</div>

    <script>
        $(document).ready(function() {
            var ratingValue = "{{ !is_null($site->rating) ? $site->rating->rating : 0 }}";

            /* 1. Visualizing things on Hover - See next part for action on click */
            $('#stars li').on('mouseover', function() {
                var onStar = parseInt($(this).data('value'), 10); // The star currently mouse on

                // Now highlight all the stars that's not after the current hovered star
                $(this).parent().children('li.star').each(function(e) {
                    if (e < onStar) {
                        $(this).addClass('hover');
                    } else {
                        $(this).removeClass('hover');
                    }
                });
            }).on('mouseout', function() {
                $(this).parent().children('li.star').each(function(e) {
                    $(this).removeClass('hover');
                });
            });

            /* 2. Action to perform on click */
            $('#stars li').on('click', function() {
                var onStar = parseInt($(this).data('value'), 10); // The star currently selected
                var stars = $(this).parent().children('li.star');

                for (i = 0; i < stars.length; i++) {
                    $(stars[i]).removeClass('selected');
                }

                for (i = 0; i < onStar; i++) {
                    $(stars[i]).addClass('selected');
                }

                ratingValue = parseInt($('#stars li.selected').last().data('value'), 10);
            });

            $('.save-form').click(function() {

                var token = "{{ csrf_token() }}";
                var url = "{{ route('site-ratings.store') }}";
                var method = 'POST';
                var ratingID = $('#ratingID').val();

                if (ratingID) {
                    url = "{{ route('site-ratings.update', ':id') }}";
                    url = url.replace(':id', ratingID);
                    method = 'PUT';
                }

                if (ratingValue !== 0) {
                    $.easyAjax({
                        url: url,
                        container: "#save-site-rating-form",
                        type: "POST",
                        blockUI: true,
                        redirect: true,
                        data: {
                            'rating': ratingValue,
                            'project_id': {{ $site->id }},
                            'comment': $('#comment').val(),
                            '_token': token,
                            '_method': method
                        },
                        success: function () {
                            window.location.reload();
                        }
                    })
                }
            });

            $('.delete-rating').click(function() {

                var token = "{{ csrf_token() }}";
                var method = 'DELETE';
                var ratingID = $(this).data('rating-id');

                if (ratingID) {
                    url = "{{ route('site-ratings.destroy', ':id') }}";
                    url = url.replace(':id', ratingID);
                }

                $.easyAjax({
                    url: url,
                    container: "#save-site-rating-form",
                    type: "POST",
                    blockUI: true,
                    redirect: true,
                    data: {
                        'ratingID': ratingID,
                        '_token': token,
                        '_method': method
                    },
                    success: function () {
                        window.location.reload();
                    }
                })
            });

            $('.edit-rating').click(function() {
                $('#save-site-rating-form').removeClass('d-none');
                $('.rating-detail').addClass('d-none');
            });


        });
    </script>
