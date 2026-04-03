@php
$addClientCategoryPermission = user()->permission('manage_client_category');
$addClientSubCategoryPermission = user()->permission('manage_client_subcategory');
@endphp

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form" method="PUT">
            <div class="add-customer bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('modules.cleaners.accountDetails')</h4>

                <div class="row p-20">
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-md-4">
                                <x-forms.select fieldId="salutation" fieldName="salutation"
                                    :fieldLabel="__('modules.customer.salutation')">
                                    <option value="">--</option>
                                    @foreach ($salutations as $salutation)
                                        <option value="{{ $salutation->value }}" @selected($customer->salutation == $salutation)>{{ $salutation->label() }}</option>
                                    @endforeach
                                </x-forms.select>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <x-forms.text fieldId="name" :fieldLabel="__('modules.customer.clientName')" fieldName="name"
                                    fieldRequired="true" :fieldPlaceholder="__('placeholders.name')"
                                    :fieldValue="$customer->name">
                                </x-forms.text>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <x-forms.email fieldId="email" :fieldLabel="__('app.email')" fieldName="email"
                                    :popover="__('modules.customer.emailNote')" :fieldPlaceholder="__('placeholders.email')"
                                    :fieldValue="$customer->email" :fieldReadOnly="!is_null($customer->email)" :popover="__('superadmin.emailCannotChange')">
                                </x-forms.email>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <x-forms.select fieldId="country" :fieldLabel="__('app.country')" fieldName="country"
                                search="true">
                                <option value="">--</option>
                                @foreach ($countries as $item)
                                    <option @selected($customer->country_id == $item->id) data-mobile="{{ $customer->mobile }}" data-tokens="{{ $item->iso3 }}" data-phonecode="{{ $item->phonecode }}" data-content="<span
                                        class='flag-icon flag-icon-{{ strtolower($item->iso) }} flag-icon-squared'></span>
                                        {{ $item->nicename }}" data-iso="{{ $item->iso }}" value="{{ $item->id }}">{{ $item->nicename }}</option>
                                @endforeach
                            </x-forms.select>
                        </div>
                        <div class="col-md-4">
                            <x-forms.label class="my-3" fieldId="mobile"
                                :fieldLabel="__('app.mobile')"></x-forms.label>
                            <x-forms.input-group style="margin-top:-4px">
                                <x-forms.select fieldId="country_phonecode" fieldName="country_phonecode"
                                    search="true">
                                    @foreach ($countries as $item)
                                        <option @selected($customer->country_phonecode == $item->phonecode && !is_null($item->numcode))
                                                data-tokens="{{ $item->name }}" data-country-iso="{{ $item->iso }}"
                                                data-content="{{$item->flagSpanCountryCode()}}"
                                                value="{{ $item->phonecode }}">
                                        </option>
                                    @endforeach
                                </x-forms.select>
                                <input type="tel" class="form-control height-35 f-14" placeholder="@lang('placeholders.mobile')"
                                    name="mobile" id="mobile" value="{{ $customer->mobile }}">
                            </x-forms.input-group>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">

                        <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                            :fieldLabel="__('modules.profile.profilePicture')"
                            :fieldValue="$customer->image_url" fieldName="image"
                            fieldId="image" fieldHeight="119" :popover="__('team chat.fileFormat.ImageFile')" />
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="gender" :fieldLabel="__('modules.cleaners.gender')"
                            fieldName="gender">
                            <option value="male" {{ $customer->gender == 'male' ? 'selected' : '' }}>@lang('app.male')
                            </option>
                            <option value="female" {{ $customer->gender == 'female' ? 'selected' : '' }}>
                                @lang('app.female')</option>
                            <option value="others" {{ $customer->gender == 'others' ? 'selected' : '' }}>
                                @lang('app.others')</option>
                        </x-forms.select>
                    </div>

                    <div class="col-md-3">
                        <x-forms.select fieldId="locale" :fieldLabel="__('modules.accountSettings.changeLanguage')"
                            fieldName="locale" search="true">
                            @foreach ($languages as $language)
                                <option @selected($customer->locale == $language->language_code)
                                data-content="<span class='flag-icon flag-icon-{{ ($language->flag_code == 'en') ? 'gb' : $language->flag_code }} flag-icon-squared'></span> {{ $language->language_name }}"
                                value="{{ $language->language_code }}">{{ $language->language_name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="category"
                            :fieldLabel="__('modules.customer.clientCategory')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="category_id" id="category_id"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach($categories as $category)
                                    <option @selected($customer->clientDetails->category_id == $category->id) value="{{ $category->id }}">
                                        {{ $category->category_name }}</option>
                                @endforeach
                            </select>

                            @if ($addClientCategoryPermission == 'all' || $addClientCategoryPermission == 'added' || $addClientCategoryPermission == 'both')
                                <x-slot name="append">
                                    <button id="addClientCategory" type="button"
                                        class="btn btn-outline-secondary border-grey"
                                        data-toggle="tooltip" data-original-title="{{ __('app.add').' '.__('modules.customer.clientCategory') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-3">
                        <x-forms.label class="mt-3" fieldId="sub_category_id"
                            :fieldLabel="__('modules.customer.clientSubCategory')"></x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="sub_category_id" id="sub_category_id"
                                data-live-search="true">
                                <option value="">--</option>
                                @forelse($subcategories as $subcategory)
                                    <option  @selected($customer->clientDetails->sub_category_id == $subcategory->id) value="{{ $subcategory->id }}">
                                        {{ $subcategory->category_name }}</option>
                                @empty
                                    <option value="">@lang('team chat.noCategoryAdded')</option>
                                @endforelse
                            </select>

                            @if ($addClientSubCategoryPermission == 'all' || $addClientSubCategoryPermission == 'added' || $addClientSubCategoryPermission == 'both')
                                <x-slot name="append">
                                    <button id="addClientSubCategory" type="button"
                                        class="btn btn-outline-secondary border-grey"
                                        data-toggle="tooltip" data-original-title="{{ __('app.add').' '.__('modules.customer.clientSubCategory') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 w-100 mt-3"
                                for="usr">@lang('modules.customer.clientCanLogin')</label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="login-yes" :fieldLabel="__('app.yes')" fieldName="login"
                                    fieldValue="enable" :checked="($customer->login == 'enable') ? 'checked' : ''">
                                </x-forms.radio>
                                <x-forms.radio fieldId="login-no" :fieldLabel="__('app.no')" fieldValue="disable"
                                    fieldName="login" :checked="($customer->login == 'disable') ? 'checked' : ''">
                                </x-forms.radio>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 w-100 mt-3"
                                for="usr">@lang('modules.emailSettings.emailNotifications')</label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="notification-yes" :fieldLabel="__('app.yes')" fieldValue="yes"
                                    fieldName="sendMail" checked="($customer->email_notifications) ? 'checked' : ''">
                                </x-forms.radio>
                                <x-forms.radio fieldId="notification-no" :fieldLabel="__('app.no')" fieldValue="no"
                                    fieldName="sendMail" :checked="(!$customer->email_notifications) ? 'checked' : ''">
                                </x-forms.radio>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group my-3">
                            <label class="f-14 text-dark-grey mb-12 w-100 mt-3" for="usr">@lang('app.status')</label>
                            <div class="d-flex">
                                <x-forms.radio fieldId="status-active" :fieldLabel="__('app.active')"
                                    fieldValue="active" fieldName="status"
                                    checked="($customer->status == 'active') ? 'checked' : ''">
                                </x-forms.radio>
                                <x-forms.radio fieldId="status-inactive" :fieldLabel="__('app.inactive')"
                                    fieldValue="deactive" fieldName="status"
                                    :checked="($customer->status == 'deactive') ? 'checked' : ''">
                                </x-forms.radio>
                            </div>
                        </div>
                    </div>


                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-top-grey">
                    @lang('modules.customer.companyDetails')</h4>
                <div class="row p-20">
                    <div class="col-lg-3 col-md-6">
                        <x-forms.text class="mb-3 mt-3 mt-lg-0 mt-md-0" fieldId="company_name"
                            :fieldLabel="__('modules.customer.companyName')" fieldName="company_name"
                            :fieldValue="$customer->clientDetails->company_name" :fieldPlaceholder="__('placeholders.company')">
                        </x-forms.text>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.text class="mb-3 mt-3 mt-lg-0 mt-md-0" fieldId="website"
                            :fieldLabel="__('modules.customer.website')" fieldName="website"
                            :fieldValue="$customer->clientDetails->website"
                            :fieldPlaceholder="__('placeholders.website')">
                        </x-forms.text>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.text class="mb-3 mt-3 mt-lg-0 mt-md-0" fieldId="tax_name"
                            :fieldLabel="__('app.taxName')" :fieldValue="$customer->clientDetails->tax_name"
                            fieldName="tax_name" :fieldPlaceholder="__('placeholders.gst/vat')"></x-forms.text>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.text class="mb-3 mt-3 mt-lg-0 mt-md-0" fieldId="gst_number"
                            :fieldLabel="__('app.gstNumber')" :fieldValue="$customer->clientDetails->gst_number"
                            fieldName="gst_number" :fieldPlaceholder="__('placeholders.gstNumber')"></x-forms.text>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text fieldId="office" :fieldLabel="__('modules.customer.officePhoneNumber')"
                            fieldName="office" :fieldPlaceholder="__('placeholders.mobileWithPlus')"
                            :fieldValue="$customer->clientDetails->office"></x-forms.text>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.text fieldId="city" :fieldLabel="__('modules.stripeCustomerAddress.city')"
                            fieldName="city" :fieldPlaceholder="__('placeholders.city')"
                            :fieldValue="$customer->clientDetails->city"></x-forms.text>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.text fieldId="state" :fieldLabel="__('modules.stripeCustomerAddress.state')"
                            fieldName="state" :fieldPlaceholder="__('placeholders.state')"
                            :fieldValue="$customer->clientDetails->state"></x-forms.text>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.text fieldId="postalCode" :fieldLabel="__('modules.stripeCustomerAddress.postalCode')"
                            fieldName="postal_code" :fieldPlaceholder="__('placeholders.postalCode')"
                            :fieldValue="$customer->clientDetails->postal_code"></x-forms.text>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2"
                                :fieldLabel="__('modules.accountSettings.companyAddress')" fieldName="address"
                                fieldId="address" :fieldPlaceholder="__('placeholders.address')"
                                :fieldValue="$customer->clientDetails->address">
                            </x-forms.textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.shippingAddress')"
                                :fieldValue="$customer->clientDetails->shipping_address" fieldName="shipping_address"
                                fieldId="shipping_address" :fieldPlaceholder="__('placeholders.address')">
                            </x-forms.textarea>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text fieldId="skype" fieldLabel="Skype" fieldName="skype"
                            :fieldPlaceholder="__('placeholders.customer.skype')" :fieldValue="$customer->clientDetails->skype">
                        </x-forms.text>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.text fieldId="linkedin" fieldLabel="Linkedin" fieldName="linkedin"
                            :fieldPlaceholder="__('placeholders.customer.linkedin')"
                            :fieldValue="$customer->clientDetails->linkedin"></x-forms.text>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.text fieldId="twitter" fieldLabel="Twitter" fieldName="twitter"
                            :fieldPlaceholder="__('placeholders.customer.twitter')" :fieldValue="$customer->clientDetails->twitter">
                        </x-forms.text>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.text fieldId="facebook" fieldLabel="Facebook" fieldName="facebook"
                            :fieldPlaceholder="__('placeholders.customer.facebook')"
                            :fieldValue="$customer->clientDetails->facebook"></x-forms.text>
                    </div>

                    @if ($editPermission == 'all')
                        <div class="col-lg-3 col-md-6">
                            <x-forms.select fieldId="added_by" :fieldLabel="__('app.added').' '.__('app.by')"
                                fieldName="added_by">
                                <option value="">--</option>
                                @foreach ($cleaners as $item)
                                    @if($item->status == 'active' || $customer->clientDetails->added_by == $item->id)
                                        <x-user-option :user="$item" :selected="$customer->clientDetails->added_by == $item->id" />
                                    @endif
                                @endforeach
                            </x-forms.select>
                        </div>
                    @endif

                    @if (function_exists('sms_setting') && sms_setting()->telegram_status)
                        <div class="col-md-6">
                            <x-forms.number fieldName="telegram_user_id" fieldId="telegram_user_id"
                                fieldLabel="<i class='fab fa-telegram'></i> {{ __('sms::modules.telegramUserId') }}"
                                :fieldValue="$customer->telegram_user_id" :popover="__('sms::modules.userIdInfo')" />
                            <p class="text-bold text-danger">
                                @lang('sms::modules.telegramBotNameInfo')
                            </p>
                            <p class="text-bold"><span id="telegram-link-text">https://t.me/{{ sms_setting()->telegram_bot_name }}</span>
                                <a href="javascript:;" class="btn-copy btn-secondary f-12 rounded p-1 py-2 ml-1"
                                    data-clipboard-target="#telegram-link-text">
                                    <i class="fa fa-copy mx-1"></i>@lang('app.copy')</a>
                                <a href="https://t.me/{{ sms_setting()->telegram_bot_name }}" target="_blank" class="btn-secondary f-12 rounded p-1 py-2 ml-1">
                                    <i class="fa fa-copy mx-1"></i>@lang('app.openInNewTab')</a>
                            </p>
                        </div>
                    @endif

                    <div class="col-lg-12">
                        <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2"
                                               :fieldLabel="__('modules.service agreements.companyLogo')" fieldName="company_logo"
                                               :fieldValue=" ($customer->clientDetails->company_logo ? $customer->clientDetails->image_url : null)" fieldId="company_logo" :popover="__('team chat.fileFormat.ImageFile')"/>
                    </div>
                </div>
                @includeIf('einvoice::form.customer-edit')

                <x-forms.custom-field :fields="$fields" :model="$clientDetail"></x-forms.custom-field>

                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('customers.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

@if (function_exists('sms_setting') && sms_setting()->telegram_status)
    <script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>
@endif
<script>
    $(document).ready(function() {

        $('#random_password').click(function() {
            const randPassword = Math.random().toString(36).substr(2, 8);

            $('#password').val(randPassword);
        });

        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        function updatePhoneCode() {
            var selectedCountry = $('#country').find(':selected');
            var phonecode = selectedCountry.data('phonecode');
            var iso = selectedCountry.data('iso');

            $('#country_phonecode').find('option').each(function() {
                if ($(this).data('country-iso') === iso) {
                    $(this).val(phonecode);
                    $(this).prop('selected', true); // Set the option as selected
                }
            });
        }
        updatePhoneCode();

        $('#country').change(function(){
            updatePhoneCode();
            $('.select-picker').selectpicker('refresh');
        });


        // Function to load subcategories based on selected category
        function loadSubCategories(categoryId, selectedSubCategoryId = null) {

            if (categoryId === '') {
                $('#sub_category_id').html('<option value="">--</option>');
                $('#sub_category_id').selectpicker('refresh');
                return; // Stop further execution if no category is selected
            }

            var url = "{{ route('get_client_sub_categories', ':id') }}";
            url = url.replace(':id', categoryId);

            $.easyAjax({
                url: url,
                type: "GET",
                success: function(response) {
                    if (response.status == 'success') {
                        var options = [];
                        var rData = response.data;

                        $.each(rData, function(index, value) {
                            var isSelected = selectedSubCategoryId && selectedSubCategoryId == value.id ? 'selected' : '';
                            var selectData = '<option value="' + value.id + '" ' + isSelected + '>' + value.category_name + '</option>';
                            options.push(selectData);
                        });

                        $('#sub_category_id').html('<option value="">--</option>' + options);
                        $('#sub_category_id').selectpicker('refresh');
                    }
                }
            });
        }

        // On change of category, fetch subcategories
        $('#category_id').change(function() {
            var categoryId = $(this).val();
            loadSubCategories(categoryId);
        });

        // Pre-load subcategories in the edit form
        var selectedCategoryId = "{{ $customer->clientDetails->category_id }}";
        var selectedSubCategoryId = "{{ $customer->clientDetails->sub_category_id }}";

        loadSubCategories(selectedCategoryId, selectedSubCategoryId);


        $('#save-form').click(function() {
            const url = "{{ route('customers.update', $customer->id) }}";

            $.easyAjax({
                url: url,
                container: '#save-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                file: true,
                buttonSelector: "#save-form",
                data: $('#save-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = response.redirectUrl;
                    }
                }
            })
        });

        $('#addClientCategory').click(function() {
            const url = "{{ route('clientCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        })
        $('#addClientSubCategory').click(function() {
            const url = "{{ route('clientSubCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        })

        <x-forms.custom-field-filejs/>

        init(RIGHT_MODAL);
    });

    @if (function_exists('sms_setting') && sms_setting()->telegram_status)
        var clipboard = new ClipboardJS('.btn-copy');

        clipboard.on('success', function(e) {
            Swal.fire({
                icon: 'success',
                text: '@lang("app.urlCopied")',
                toast: true,
                position: 'top-end',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                customClass: {
                    confirmButton: 'btn btn-primary',
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
            })
        });
    @endif
</script>
