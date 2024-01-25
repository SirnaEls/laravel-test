@extends('layouts.restaurant_admin', ['admin_dashboard'=>true])
@section('title') ODK @stop
@section('page')
    <div class="nav_arrows right_space">
        <a href="{{route('admin.dlc_basket.index')}}" class="arrows left_arrow active"></a>
        <div class="arrows right_arrow"></div>
        <div class="section_steps">
            <div class="steps active">
                <div class="main_contentWrap">
                    <h3 class="steps_title secondLevel">
                        <span><img src="{{asset('img/print_label_titleIcon.png')}}" alt="" /></span>
                        <span><img src="{{asset('img/parameters_titleIcon.png')}}" alt="" /></span>
                        <em>IMPRESSION DES DLC SECONDAIRES</em>
                    </h3>
                    @if($basket->print_format == 'simple')
                        <p class="info">Nécessite la version 2.3.2 (ou ultérieure) de l'application.</p>
                    @endif
                    <form method="post" action="{{ $form_action }}" class="admin_formStyle" id="dlcBasketForm" autocomplete="off" >
                        {{ csrf_field() }}
                        <div class="form_section">
                            <fieldset class="clearfix">
                                <label><span>Veuillez indiquer le nom du nouveau panier</span></label>
                                <div class="field_wrap clearfix">
                                    <div class="field_border border_left">
                                        <input type="text" name="name" value="{{ old('name', $basket->name) }}" maxlength="50" placeholder="NOM DU PANIER" />
                                    </div>
                                </div>
                            </fieldset>
                            @if (!$basket->is_fixed)
                                <fieldset class="clearfix">
                                    <label><span>Information sur l’étiquette</span></label>
                                    <div class="field_wrap admin_selectWrap clearfix">
                                        <div class="field_border border_left">
                                            <select name="print_label" class="admin_select">
                                                @foreach( ['ENT/FAB. LE', 'DÉCONGELÉ LE', 'SURGELÉ LE'] as $label) 
                                                <option {{ old('print_label', $basket->print_label) == $label ? 'selected' : '' }} value="{{ $label }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset class="clearfix">
                                    <label><span>Format de l’étiquette (imprimante Epson uniquement)</span></label>
                                    <div class="field_wrap admin_selectWrap clearfix">
                                        <div class="field_border border_left">
                                            <select name="print_format" class="admin_select">
                                                @foreach( ['default' => 'ÉCO (longueur : 26mm)', 'full' => 'LARGE (longueur selon produit)'] as $key => $label) 
                                                <option {{ old('print_format', $basket->print_format) == $key ? 'selected' : '' }} value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset class="clearfix">
                                    <label><span>Afficher les images des produits</span></label>
                                    <div class="field_wrap clearfix">
                                        <div class="field_border">
                                            <span class="custom_delete_checkbox display-inline @if($nbImages==0) inactive @endif">
                                                @if ($nbImages==0) (Vos produits n’ont pas d’image) @endif
                                                <input type="hidden" name="show_images" value="{{ $basket->show_images }}" @if($basket->show_images) checked @endif />
                                                <input type="checkbox" @if($nbImages==0) disabled @endif class="custom_checkbox" @if($basket->show_images) checked @endif />
                                            </span>
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset class="clearfix">
                                    <label><span>Ajouter l'heure précise sur l'étiquette</span></label>
                                    <div class="field_wrap clearfix">
                                        <div class="field_border">
                                            <span class="custom_delete_checkbox display-inline">
                                                <input type="hidden" name="print_minutes" value="{{ $basket->print_minutes }}" @if($basket->print_minutes) checked @endif />
                                                <input type="checkbox" class="custom_checkbox" @if($basket->print_minutes) checked @endif />
                                            </span>
                                        </div>
                                    </div>
                                </fieldset>
                            @endif
                            <fieldset class="clearfix">
                                <label><span>Couleur</span></label>
                                <div class="field_wrap field_space checkboxSet_gender">
                                    <div class="checkboxSet cols_3 clearfix">
                                        <div class="row clearfix">
                                            <?php $index = 0; ?>
                                            @foreach($colors as $color=>$text)
                                            <div class="column">
                                                <div class="checkboxSet_element">
                                                    <input @if(old('color', $basket->color) == $color ) checked="checked" @endif type="radio" name="color" value="{{$color}}" />
                                                    <span>{{ $text }}</span>
                                                </div>
                                            </div>
                                            @if(($index+1)%3==0)
                                        </div>
                                        <div class="row clearfix">
                                            @endif
                                            <?php $index++; ?>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset class="clearfix">
                                <label><span>Veuillez choisir le pictogramme associé à ce panier</span></label>
                                <div class="field_wrap field_space checkboxSet_box w-60-p">
                                    <div class="checkboxSet cols_5 checkboxIcons clearfix">
                                        @foreach($icons as $icon)
                                            <div class="column">
                                                <label class="checkboxSet_element"><input @if($icon == old('icon', $basket->icon)) checked @endif type="radio" name="icon" value="{{ $icon }}" /> <img src="{{ asset($icon) }}" /></label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                        <div class="form_errorSection">
                            @foreach($errors->all() as $error)
                                <label>{{$error}}</label>
                            @endforeach
                        </div>
                        <div class="align_center">
                            <button type="submit" class="btn fixWidth inactive icon_btn"><span>Je valide</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('javascripts')
    <script src="{{asset('js/jquery.ezmark.min.js')}}"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $('.custom_checkbox').ezMark();

            // l'erreur se trouve ici, car nous étions sur un closest(form) et il allait donc chercher tout le form et changer la valeur des deux inputs.
            // en passant le closest en fieldset, on tape dans fieldset et non dans tout le form ce qui évite de modifier la valeur des deux inputs à la fois.
            $('.custom_checkbox').change(function(){
                $(this).closest('fieldset').find('input:hidden').val($(this).is(':checked') ? 1 : 0);
            });

            $("#dlcBasketForm").validate({
                ignore: '',
                rules:{
                    name: {
                        required: true
                    },
                    icon: {
                        required: true
                    },
                }, groups: {
                    requiredFields: "name icon"
                }, messages: {
                    name: {
                        required: "Ces champs sont obligatoires"
                    },
                    icon: {
                        required: "Ces champs sont obligatoires"
                    }
                },
                highlight: function(element, errorClass, validClass) {
                    if($(element).is('input:radio') || $(element).is('input:checkbox') ){
                        $(element).closest('.checkboxSet').find('.checkboxSet_element').addClass(errorClass).removeClass(validClass);
                    }
                    else{
                        $(element).addClass(errorClass).removeClass(validClass);
                    }
                },
                unhighlight: function(element, errorClass, validClass) {
                    if($(element).is('input:radio') || $(element).is('input:checkbox') ){
                        $(element).closest('.checkboxSet').find('.checkboxSet_element').removeClass(errorClass).addClass(validClass);
                    }
                    else{
                        $(element).removeClass(errorClass).addClass(validClass);
                    }
                },
                errorPlacement: function(error, element) {
                    error.appendTo(".form_errorSection");
                },onkeyup: function (element) {
                    if ($("#dlcBasketForm").validate().checkForm()) {
                        $("#dlcBasketForm button").removeClass('inactive');
                        $("#dlcBasketForm button").addClass('active');
                    }else{
                        $("#dlcBasketForm button").addClass('inactive');
                        $("#dlcBasketForm button").removeClass('active');
                    }
                }
            });

            $("#dlcBasketForm").find('input:radio, input:checkbox').change(function(){
                if ($("#dlcBasketForm").validate().checkForm()) {
                    $("#dlcBasketForm button").removeClass('inactive');
                    $("#dlcBasketForm button").addClass('active');
                }else{
                    $("#dlcBasketForm button").addClass('inactive');
                    $("#dlcBasketForm button").removeClass('active');
                }
            });

            $('.checkboxSet .checkboxSet_element').click(function(){
                if($(this).hasClass('inactive')){
                    return;
                }

                if($(this).find('input[type="radio"]').length){
                    $(this).closest('.checkboxSet').find('.checkboxSet_element').removeClass('active');
                    $(this).closest('.checkboxSet').find('.checkboxSet_element').find('input[type="radio"]').removeAttr('checked');
                    $(this).addClass('active');
                    $(this).find('input[type="radio"]')[0].checked = true;

                    if($(this).find('input').attr('name')=='deadline'){
                        checkDisabled();
                    }
                }else{
                    if($(this).hasClass('active')){
                        $(this).removeClass('active');
                        $(this).find('input[type="checkbox"]')[0].checked = false;
                    }else{
                        $(this).addClass('active');
                        $(this).find('input[type="checkbox"]')[0].checked = true;
                    }
                }
            });
            $('input:radio:checked').closest('.checkboxSet_element').addClass('active');
            $('input:checkbox:checked').closest('.checkboxSet_element').addClass('active');
            

            if ($("#dlcBasketForm").validate().checkForm()) {
                $("#dlcBasketForm button").removeClass('inactive');
                $("#dlcBasketForm button").addClass('active');
            }else{
                $("#dlcBasketForm button").addClass('inactive');
                $("#dlcBasketForm button").removeClass('active');
            }
        });
    </script>
@stop