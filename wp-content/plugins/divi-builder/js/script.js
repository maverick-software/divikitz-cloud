var intervl = '';

jQuery(document).ready(function ($) {

    $(".page-id-965 .product-name a").attr('href',"javascript:void(0)");
    $(".page-id-964 .woocommerce-order p > a").attr('href',"https://app.divibloxx.com/billing/");
    $(".page-id-964 .product-name a").attr('href',"javascript:void(0)");





    // <!-- Section JS -->


    //Duplicate Page JS
    $("body").on("click", '.user_actions .duplicate_section a', function () {
        var $this = $(this);
        var dpl_nm = $this.attr('data-name');
        var dpl_id = $this.attr('data-id');
        var dpl_msg = $this.attr('data-title');
        var dpl_catid = $this.parent().attr('id');

        Swal.fire({
            title: 'Are you sure?',
            text: dpl_msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#9F05C5',
            cancelButtonColor: '#000',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                var star_id = $this.attr('id');
                var ajax_url = builder.ajax_url;
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: ajax_url,
                    data: {
                        'dpl_id': dpl_id,
                        'dpl_nm': dpl_nm,
                        'dpl_catid': dpl_catid,
                        'action': 'duplicate_section_content'
                    },
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Please Wait ! <i class="fa fa-refresh fa-spin"></i>',
                            html: '', // add html attribute if you want or remove
                            allowOutsideClick: false,
                            showCancelButton: false, // There won't be any cancel button
                            showConfirmButton: false,
                            onBeforeOpen: () => {
                                Swal.showLoading();
                            },
                        });
                    },
                    success: function (resp) {
                        swal.close();
                        if (resp.code == 200) {
                            Swal.fire({
                                title: "Success!",
                                text: resp.message,
                                confirmButtonColor: '#000',
                                icon: "success"
                            });
                            window.location.href = "";
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: resp.message,
                                confirmButtonColor: '#000',
                                icon: "error"
                            });
                        }
                    },
                    error: function () {
                        swal.close();
                        Swal.fire({
                            title: "Error!",
                            text: "Please try again later",
                            confirmButtonColor: '#000',
                            icon: "error"
                        });
                    }
                });

            }
        });
    });


    //Regenerate Post Feature Image

    $("body").on("click", '.user_actions .regenrate_section a', function () {
        var $this = $(this);
        var rgn_nm = $this.attr('data-name');
        var rgn_id = $this.attr('data-id');
        var rgn_msg = $this.attr('data-title');
        
        Swal.fire({
            title: 'Are you sure?',
            text: rgn_msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#9F05C5',
            cancelButtonColor: '#000',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                var star_id = $this.attr('id');
                var ajax_url = builder.ajax_url;
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: ajax_url,
                    data: {
                        'rgn_id': rgn_id,
                        'rgn_nm': rgn_nm,
                        'action': 'regenerate_section_image'
                    },
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Please Wait ! <i class="fa fa-refresh fa-spin"></i>',
                            html: '', // add html attribute if you want or remove
                            allowOutsideClick: false,
                            showCancelButton: false, // There won't be any cancel button
                            showConfirmButton: false,
                            onBeforeOpen: () => {
                                Swal.showLoading();
                            },
                        });
                    },
                    success: function (resp) {
                        swal.close();
                        if (resp.code == 200) {
                            Swal.fire({
                                title: "Success!",
                                text: resp.message,
                                confirmButtonColor: '#000',
                                icon: "success"
                            });
                            window.location.href = "";
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: resp.message,
                                confirmButtonColor: '#000',
                                icon: "error"
                            });
                        }
                    },
                    error: function () {
                        swal.close();
                        Swal.fire({
                            title: "Error!",
                            text: "Please try again later",
                            confirmButtonColor: '#000',
                            icon: "error"
                        });
                    }
                });

            }
        });
    });


    //Rename Section
    $("body").on("click", '.user_actions .section_rename_btn a', function () {
        var $this = $(this);
        var rn_nm = $this.attr('data-name');
        var rn_id = $this.attr('data-id');
        var rn_msg = $this.attr('data-title');

        Swal.fire({
            title: "Are you sure?",
            text: rn_msg,
            input: 'text',
            inputValue: rn_nm,
            showCancelButton: true,
            confirmButtonColor: '#9F05C5',
            cancelButtonColor: '#000',
            confirmButtonText: 'Yes'

        }).then((result) => {
            if (result.isConfirmed) {
                var star_id = $this.attr('id');
                var ajax_url = builder.ajax_url;
                var pnm = result.value;
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: ajax_url,
                    data: {
                        'rn_id': rn_id,
                        'rn_nm': pnm,
                        'action': 'section_rename_ajax'
                    },
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Please Wait ! <i class="fa fa-refresh fa-spin"></i>',
                            html: '', // add html attribute if you want or remove
                            allowOutsideClick: false,
                            showCancelButton: false, // There won't be any cancel button
                            showConfirmButton: false,
                            onBeforeOpen: () => {
                                Swal.showLoading();
                            },
                        });
                    },
                    success: function (resp) {
                        swal.close();
                        if (resp.code == 200) {
                            Swal.fire({
                                title: "Success!",
                                text: resp.message,
                                confirmButtonColor: '#000',
                                icon: "success"
                            });
                            window.location.href = "";
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: resp.message,
                                confirmButtonColor: '#000',
                                icon: "error"
                            });
                        }
                    },
                    error: function () {
                        swal.close();
                        Swal.fire({
                            title: "Error!",
                            text: "Please try again later",
                            confirmButtonColor: '#000',
                            icon: "error"
                        });
                    }
                });

            }
        });
    });


    // Delete Section
    $("body").on("click", ".user_actions .del_saction", function () {
        var inputValue = $(this).attr('id');
        var redirect_url = $(this).attr('data-id');
        var del_msg = $(this).attr('data_title');
        var del_nm= $(this).attr('data_nm');
        Swal.fire({
            title: del_msg,
            allowOutsideClick: false,
            confirmButtonText: "Delete Section",
            confirmButtonColor: "#000",
            customClass: 'swal-wide',
            showCancelButton: true,
            cancelButtonText: 'Cancel',
        }).then(function (project) {
            $(".swal-wide button.swal2-confirm").html('<i class="fa fa-star"></i> Wait');
            if (project.isConfirmed) {
                var builder_cats = project.value;
                var ajax_url = builder.ajax_url;
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: ajax_url,
                    data: {
                        action: 'builder_custom_section',
                        del_name: del_nm,
                        section_id: inputValue
                    },

                    beforeSend: function () {
                        Swal.fire({
                            title: 'Please Wait ! <i class="fa fa-refresh fa-spin"></i>',
                            html: '', // add html attribute if you want or remove
                            allowOutsideClick: false,
                            showCancelButton: false, // There won't be any cancel button
                            showConfirmButton: false,
                            onBeforeOpen: () => {
                                Swal.showLoading();
                            },
                        });
                    },

                    success: function (resp) {
                        $(".swal-wide button.swal2-confirm").html('Add Project');
                        if (resp.code == 200) {
                            Swal.fire({
                                title: "Success!",
                                text: resp.message,
                                confirmButtonColor: '#000',
                                icon: "success"
                            });
                            window.location.href = redirect_url;
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: resp.message,
                                confirmButtonColor: '#000',
                                icon: "error"
                            });
                        }
                    },
                    error: function () {
                        $(".swal-wide button.swal2-confirm").html('Add Project');
                        Swal.fire({
                            title: "Error!",
                            text: "Please try again later",
                            confirmButtonColor: '#000',
                            icon: "error"
                        });
                    }
                });
            }
        });
    });




    $("body").on("click", ".manage_card_toggle_profile", function (e) {
        var $this = $(this);
        if ($this.hasClass('card_active')) {
            $this.html("Add Card");
            $this.removeClass('card_active');
            $(".card_add").hide();
            $(".paymentList").fadeIn("slow");
        } else {
            $this.html("Card Listing");
            $this.addClass('card_active');
            $(".paymentList").hide();
            $(".card_add").fadeIn("slow");
        }
    });




    //Upload Section Jquery

    




    $('body').on('click touch', '#openSlideNav', function () {
        $("#slideNav").addClass("active");
        $(this).hide();
    });

    $('body').on('click touch', '#closeSlideNav', function () {
        $(".project_details_menu li").removeClass("active");
        $("#openSlideNav").show();
        $("#slideNav").removeClass("active");
    });




    $("#user-profile").validate({
        rules: {
            pass2: {
                equalTo: "#pass1"
            }
        },
        messages: {
            pass2: "Enter Repeat Password Same as Password"
        },
        submitHandler: function (form) {
            var ajax_url = bloxx.ajax_url;
            $.ajax({
                type: "POST",
                url: ajax_url,
                dataType: "json",
                data: $("#user-profile").serialize(),
                beforeSend: function () {
                    $("#user-profile #update_info").prop('disabled', true);
                    $("#user-profile #update_info").html('<i class="fa fa-spinner fa-spin"></i>');
                    swal.fire({
                        customClass: {
                            container: 'swal2_spinner',
                        },
                        html: '<div class="builder_spinner" id="loadingSpinner"></div>',
                        showConfirmButton: false,
                        onRender: function () {
                            $('.swal2-content').prepend(sweet_loader);
                        }
                    });
                },
                success: function (resp) {
                    swal.close();
                    $("#user-profile #update_info").prop('disabled', false);
                    $("#user-profile #update_info").html('Submit');

                    if (resp.code == 200) {
                        $("#pass1").val('');
                        $("#pass2").val('');
                        Swal.fire({
                            title: "Success!",
                            text: resp.message,
                            confirmButtonColor: '#000',
                            icon: "success"
                        });
                    } else {
                        Swal.fire({
                            title: "Error!",
                            text: resp.message,
                            confirmButtonColor: '#000',
                            icon: "error"
                        });
                    }
                },
                error: function () {
                    $("#user-profile #update_info").prop('disabled', false);
                    $("#user-profile #update_info").html('Submit');

                    Swal.fire({
                        title: "Error!",
                        text: "Please try again later",
                        confirmButtonColor: '#000',
                        icon: "error"
                    });
                }
            });
        }
    });



    

    $("#bill_profile").validate({
        rules: {
            bill_eml: {
                email: true
            }
        },
        submitHandler: function (form) {
            var ajax_url = bloxx.ajax_url;
            $.ajax({
                type: "POST",
                url: ajax_url,
                dataType: "json",
                data: $("#bill_profile").serialize(),
                beforeSend: function () {
                    $("#bill_profile #update_info").prop('disabled', true);
                    $("#bill_profile #update_info").html('<i class="fa fa-spinner fa-spin"></i>');
                    swal.fire({
                        customClass: {
                            container: 'swal2_spinner',
                        },
                        html: '<div class="builder_spinner" id="loadingSpinner"></div>',
                        showConfirmButton: false,
                        onRender: function () {
                            $('.swal2-content').prepend(sweet_loader);
                        }
                    });
                },
                success: function (resp) {
                    swal.close();
                    $("#bill_profile #update_info").prop('disabled', false);
                    $("#bill_profile #update_info").html('Submit');

                    if (resp.code == 200) {
                        $("#pass1").val('');
                        $("#pass2").val('');
                        Swal.fire({
                            title: "Success!",
                            text: resp.message,
                            confirmButtonColor: '#000',
                            icon: "success"
                        });
                    } else {
                        Swal.fire({
                            title: "Error!",
                            text: resp.message,
                            confirmButtonColor: '#000',
                            icon: "error"
                        });
                    }
                },
                error: function () {
                    $("#bill_profile #update_info").prop('disabled', false);
                    $("#bill_profile #update_info").html('Submit');

                    Swal.fire({
                        title: "Error!",
                        text: "Please try again later",
                        confirmButtonColor: '#000',
                        icon: "error"
                    });
                }
            });
        }
    });


    const listViewButton = document.querySelector('.list-view-button');
    const gridViewButton = document.querySelector('.grid-view-button');
    const list = document.querySelectorAll('div.list');

    $(document).on('click', ".list-view-button", function () {
        $(this).addClass('active').prev().removeClass('active');
        if ($(this).parents('.filter-options').next().hasClass('list')) {
            $(this).parents('.filter-options').next().removeClass('grid-view-filter').addClass('list-view-filter');
        } else {
            $(this).parents('.filter-options').next().children('.list').removeClass('grid-view-filter').addClass('list-view-filter');
            $(this).parent().prev('button').html('<i class="fa fa-bars"></i>');
        }
    });



    $(document).on('click', ".grid-view-button", function () {
        $(this).addClass('active').next().removeClass('active');
        if ($(this).parents('.filter-options').next().hasClass('list')) {
            $(this).parents('.filter-options').next().addClass('grid-view-filter').removeClass('list-view-filter');
        } else {
            $(this).parents('.filter-options').next().children('.list').addClass('grid-view-filter').removeClass('list-view-filter');
            $(this).parent().prev('button').html('<i class="fa fa-th-large"></i>');
        }
    });


    $('.panel').hide();
    $(".panelContainer .panel:first-child").show();
    $('.tabs a').click(function () {
        $('.panel').hide();
        $(".spinner_loader").show();
        $('.tabs a.active').removeClass('active');
        $(this).addClass('active');
        var panel = $(this).attr('href');
        $(panel).fadeIn(1000);
    }); // end click



    




    //  //Left to slider
    $(document).on("click", ".open-sidebar", function (e) {
        $(".switch-sidebar.active").trigger("click");
        if ($(this).hasClass("active")) {
            $("#leftCategorySidebar").removeClass('sidebar-in');
            $("#leftCategorySidebar").removeAttr("style");
            $(this).removeClass("active");
            $(".builder_posts").css({
                'overflow-y': 'scroll',
                'left': '360px'
            });
            $(".builder_posts").hide();

        } else {

            $("#leftCategorySidebar").addClass('sidebar-in');
            $("#leftCategorySidebar").attr("style", "left:60px");
            $(this).addClass("active");
        }

    });


    $('.buttonView').click(function () {
        //$('.dropdownList').slideUp();
        $(this).next('.dropdownList').slideToggle();
    });





    //BLOXX Plugin API  KEY GENERATED
    $("body").on("click", ".create_apis", function (e) {
        e.preventDefault();
        var api_limit= builder.api_limit.api;
        if(api_limit==0){
            exceed_plan_limit("You've reached maximum number of API. Please purchase/upgrade your plan.");
        } else {
            var $this = $(this);
            var get_ajax=$this.html();
            
            var ajax_url = builder.ajax_url;
            $.ajax({
                type: "POST",
                url: ajax_url,
                dataType: "json",
                data: {
                    'action': 'bloxx_generated_key'                
                },
                beforeSend: function () {
                    $this.html('<i class="fa fa-spinner fa-spin"></i>');
                },
                success: function (resp) {
                    if(resp.code==200){
                        Swal.fire({
                            title: "Success!",
                            text: resp.message,
                            confirmButtonColor: '#000',
                            icon: "success"
                        });
                        window.location.href="";
                    } else {
                        $this.html(get_ajax);
                        Swal.fire({
                            title: "Error!",
                            text: resp.message,
                            confirmButtonColor: '#000',
                            icon: "error"
                        });
                    }
                },
                error: function () {
                    $this.html(get_ajax);
                    Swal.fire({
                        title: "Error!",
                        text: "Please try again later",
                        confirmButtonColor: '#000',
                        icon: "error"
                    });
                }
            });
        }        
    });



    $("body").on("click", ".user_action_api a", function (e) {
        e.preventDefault();
        var $this = $(this);
        var get_text=$this.html();
        var user_action=$this.attr('data-title');
        var action_key=$this.attr('id');

        if(user_action=="regenerate_key"){
            var confirm_msg= "You want to regenerate this API key? All existing site connections will be lost.";
        } else {
            var confirm_msg= "You want to delete this API key? All existing site connections will be lost.";
        }

        Swal.fire({
            title: 'Are you sure?',
            text: confirm_msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#000',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {                  
            var ajax_url = builder.ajax_url;
                $.ajax({
                    type: "POST",
                    url: ajax_url,
                    dataType: "json",
                    data: {
                        'action': 'useraction_api',
                        'type': user_action,
                        'type_id': action_key
                    },
                    beforeSend: function () {
                        $this.html('<i class="fa fa-spinner fa-spin"></i>');
                        swal.fire({
                            customClass: {
                                container: 'swal2_spinner',
                            },
                            html: '<div class="builder_spinner" id="loadingSpinner"></div>',
                            showConfirmButton: false,
                            onRender: function () {
                                $('.swal2-content').prepend(sweet_loader);
                            }
                        });
                    },
                    success: function (resp) {
                        if(resp.code==200){
                            Swal.fire({
                                title: "Success!",
                                text: resp.message,
                                confirmButtonColor: '#000',
                                icon: "success"
                            });
                            window.location.href=resp.redirect_url;
                        } else {
                            $this.html(get_text);
                            Swal.fire({
                                title: "Error!",
                                text: resp.message,
                                confirmButtonColor: '#000',
                                icon: "error"
                            });
                        }
                    },
                    error: function () {
                        $this.html(get_text);
                        Swal.fire({
                            title: "Error!",
                            text: "Please try again later",
                            confirmButtonColor: '#000',
                            icon: "error"
                        });
                    }
                });
            }
        });
        
    });




    jQuery(document).on('submit','#divi_licensekey-form',function(e){
        e.preventDefault();
        var ajax_url = bloxx.ajax_url;
       // alert(ajax_url);
        $.ajax({
            type: "POST",
            url: ajax_url,
            dataType: "json",
            data: $("#divi_licensekey-form").serialize(),
            beforeSend: function () {
                $("#divi_licensekey-form #update-divi_licens").prop('disabled', true);
                $("#divi_licensekey-form #update-divi_licens").html('<i class="fa fa-spinner fa-spin"></i>');
                swal.fire({
                    customClass: {
                        container: 'swal2_spinner',
                    },
                    html: '<div class="builder_spinner" id="loadingSpinner"></div>',
                    showConfirmButton: false,
                    onRender: function () {
                        $('.swal2-content').prepend(sweet_loader);
                    }
                });
            },
            success: function (resp) {
                swal.close();
                $("#divi_licensekey-form #update-divi_licens").prop('disabled', false);
                $("#divi_licensekey-form #update-divi_licens").html('Save');

                if (resp.code == 200) {
                    
                    Swal.fire({
                        title: "Success!",
                        text: resp.message,
                        confirmButtonColor: '#000',
                        icon: "success"
                    });
                } else {
                    Swal.fire({
                        title: "Error!",
                        text: resp.message,
                        confirmButtonColor: '#000',
                        icon: "error"
                    });
                }
            },
            error: function () {
                $("#divi_licensekey-form #update-divi_licens").prop('disabled', false);
                $("#divi_licensekey-form #update-divi_licens").html('Save');

                Swal.fire({
                    title: "Error!",
                    text: "Please try again later",
                    confirmButtonColor: '#000',
                    icon: "error"
                });
            }
        });
    });





    


    if ($(window).width() < 760) {
        $(".togglebar").trigger("click");
    }



    //Hire Us Modal

    $(".modalForm").on('click', function () {
        $(".hire-model-main").addClass('model-open');
    });

    $(".modalClose, .bg-overlay").click(function () {
        $(".hire-model-main").removeClass('model-open');
    });

    $(".videoButton").on('click', function () {
        var get_url=$(this).attr('data-id');
        $(".videoModal").addClass('model-open');
        var iframe_html='<iframe width="560" height="315" src="'+get_url+'?rel=0&controls=0&showinfo=0&autoplay=true" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>'
        $(".videoModal .pop-up-content-wrap").html(iframe_html);
    });

    $(".modalClose, .bg-overlay").click(function () {
        $(".videoModal").removeClass('model-open');
        $(".videoModal .pop-up-content-wrap").html('');
    });


    //Checkout page

    $(document).on('click','.secure-check',function(){
        $("a[href=#billing_details]").trigger('click');
        $("form.woocommerce-checkout").show();
    });



    $("body").on("click", ".copy-btn", function () {    
        var keyid = $(this).attr('id');
        var generated_key = $(this).attr('data-id');
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(generated_key).select();
        document.execCommand("copy");
        $temp.remove();
        $("#alert_"+keyid).show();
        setTimeout(function(){
            $(".copyAlert").fadeOut("slow");
        }, 2000);
    });


    //Library JS


    $("body").on("click", '.user_actions .plusSign a', function () {
        //$("#library_section").fadeIn("slow");
        $("#myDropdown").slideToggle();
    });

    $("body").on("click", '.user_actions .lib_plusSign', function () {
        var get_id=$(this).attr('id');
        $(".library_section").hide();

        var assets_limit= builder.api_limit.assets;
        var uses_limit= builder.uses_limitations.use_assets;

        if(uses_limit <= assets_limit){
            console.log("#library_"+get_id+" form");

            $("#library_"+get_id+" form")[0].reset();
            $("#library_"+get_id+" form.builder_import #list_section table tbody, #library_"+get_id+" form.builder_import #list_section_page table tbody, #library_"+get_id+" form.builder_import #list_section_header table tbody, #library_"+get_id+" form.builder_import #step1_footer table tbody").html('');

            
            $("#library_"+get_id+" form.builder_import #list_section").hide();
            $("#library_"+get_id+" form.builder_import #list_section_page").hide();
            $("#library_"+get_id+" form.builder_import #list_section_header").hide();
            $("#library_"+get_id+" form.builder_import #list_section_footer").hide();
            $("#library_"+get_id+" form.builder_import #step1").show();
            $("#library_"+get_id+" form.builder_import #step1_page").show();
            $("#library_"+get_id+" form.builder_import #step1_header").show();
            $("#library_"+get_id+" form.builder_import #step1_footer").show();
            $(".user_actions .plusSign a").trigger("click");
            $("#library_"+get_id).fadeIn("slow");
        } else {
            exceed_plan_limit("You've reached maximum number of assets. Please purchase/upgrade your plan.");
        }
    });


    $("body").on("click", ".plusSign .dropbtn", function () {
        if($(this).hasClass("active")){
            $(this).removeClass("active");
            $("#myDropdown").hide();
        } else {
            $(this).addClass("active");
            $("#myDropdown").show();
        }
    });

    $("body").on("click", ".myDropdown", function(){
        $(".plusSign .dropbtn").trigger("click");
    });

    $("body").on("click", ".select_tab", function(){
        var view_id=$(this).attr('id');
        if(!$(this).hasClass("active")){
            $(".select_tab").removeClass("active");
            $(this).addClass("active");
            $(".tab_section").hide();
            $("#mytab_"+view_id).slideDown("slow");
        }
    });


    





    function handleFileSelect(evt) {
        var files = evt.target.files;
        var dataType=evt.target.getAttribute('data-id');

        
        var output = [];
        var industry_output = [];
        var header_output = [];
        var footer_output = [];

        if(dataType=="section"){
            var vcdata = $.parseJSON(cat_list);
            $.each(vcdata, function (key, value) {
                output.push('<option value="' + key + '">' + value + '</option>');
            });
        } else if(dataType=="page") {
            var vcdata = $.parseJSON(layout_list);
            $.each(vcdata, function (key, value) {
                output.push('<option value="' + key + '">' + value + '</option>');
            });

            var ind_data = $.parseJSON(industry_list);
            $.each(ind_data, function (ind_key, ind_value) {
                industry_output.push('<option value="' + ind_key + '">' + ind_value + '</option>');
            });
            var ind_dropdownlist = industry_output.join('');
        } else if(dataType=="header") {
            header_output.push('<option value="176">Header</option>');
            var head_dropdownlist = header_output.join('');
        } else {
            footer_output.push('<option value="502">Footer</option>');
            var foot_dropdownlist = footer_output.join('');
        }

        
        var dropdownlist = output.join('');


        for (var i = 0, f; f = files[i]; i++) {
            var reader = new FileReader();
            var ext = f.name.split(".");
            ext = ext[ext.length - 1].toLowerCase();
            var arrayExtensions = ["json"];
            if (arrayExtensions.lastIndexOf(ext) == -1) {
                alert("Wrong extension type.");
                return false;
            }

            if (count > 10) {
                alert("only 10 files allowed");
                return false;
            }

            reader.onload = (function (theFile) {
                return function (e) {
                    if(dataType=="section"){
                        var span = '<tr class="2"><td>' + theFile.name + '</td><td><input required type="text" name="title[' + count + ']"></td><td><select class="pc" name=project_category[' + count + ']>' + dropdownlist + '</select></td></tr>';
                    } else if(dataType=="page") {
                        var span = '<tr><td colspan="4" class="file_name_layouts">' + theFile.name + '</td></tr><tr class="2 page_type_bottmrow"><td colspan="1"><label>Title</label><input required type="text" name="title[' + count + ']"></td><td colspan="1"><label>Industry</label><select class="pc" name=ind_category[' + count + ']>' + ind_dropdownlist + '</select></td><td colspan="2"><label>Page Type</label><select class="pc" name=project_category[' + count + ']>' + dropdownlist + '</select></td></tr>';
                    } else if(dataType=="header") {
                        var span = '<tr class="2"><td>' + theFile.name + '</td><td><input required type="text" name="title[' + count + ']"></td><td><select class="pc" name=project_category[' + count + ']>' + head_dropdownlist + '</select></td></tr>';
                    } else {
                        var span = '<tr class="2"><td>' + theFile.name + '</td><td><input required type="text" name="title[' + count + ']"></td><td><select class="pc" name=project_category[' + count + ']>' + foot_dropdownlist + '</select></td></tr>';
                    }
                    count++;

                    if(dataType=="section"){
                        $("#list_section table").append(span).show();
                    } else if(dataType=="page") {
                        $("#list_section_page table").append(span).show();
                    } else if(dataType=="header") {
                        $("#list_section_header table").append(span).show();
                    } else {
                        $("#list_section_footer table").append(span).show();
                    }
                    
                };
            })(f);
            reader.readAsDataURL(f);
        }

        

        if(dataType=="section"){
            $("#library_section #step1").hide();
            $("#library_section #list_section").show();
        } else if(dataType=="page") {
            $("#library_page #step1_page").hide();
            $("#library_page #list_section_page").show();
        } else if(dataType=="header") {
            $("#library_header #step1_page").hide();
            $("#library_header #list_section_header").show();
        } else {
            $("#library_footer #step1_page").hide();
            $("#library_footer #list_section_footer").show();
        }



        // var response;

        // var fd = new FormData($(".builder_import")[0]);
        // var filess = $('#files')[0].files;
        // fd.append('files', filess);
        // fd.delete("action");
        // fd.append('action', 'builder_create_section_preview');
        // var ajax_url = builder.ajax_url;
        // $.ajax({
        //     type: "POST",
        //     async: false,
        //     dataType: "html",
        //     url: ajax_url,
        //     data: fd,
        //     contentType: false,
        //     processData: false,
        //     success: function (resp) {
        //         response = resp.trim();
        //         $(".inner_wrap_iframe iframe").attr("src", "https://cloud.neosbuilder.com/import_page/?id=" + response);
        //         // var iFrameID = document.getElementById('dd');
        //         // iFrameID.height = "";
        //         // iFrameID.height = iFrameID.contentWindow.document.body.scrollHeight + "px";
        //         $(".inner_wrap_iframe").hide();
        //         $("#library_section").hide();
        //         $('#step1_page').hide();
        //         $("#library_section_page").show();
        //         $('#list_section_page').show();
        //     },
        //     error: function () {

        //     }
        // });



    }

    var count = 0;
    $('#section_files').change(handleFileSelect);
    $('#page_files').change(handleFileSelect);
    $('#header_files').change(handleFileSelect);
    $('#footer_files').change(handleFileSelect);
    // $('#files').change(function (event){
    //     var $this=$(this);
    //     handleFileSelect($this);
    // });


    $(".builder_import").submit(function (event) {
        event.preventDefault();
        var redirect_url=$(this).attr('data-id');
        // if ($("#list_section table").is(":hidden")) {
        //     return false;
        // }

        $(".builder_import .span_error").hide();
        $(".builder_import button").html('Uploading... <i class="fa fa-spinner fa-spin" style="font-size:20px"></i>').css("pointer-events", "none");

        setTimeout(function(){
            $(".builder_import button").html('Wait... <i class="fa fa-spinner fa-spin" style="font-size:20px"></i>').css("pointer-events", "none");
        }, 2000);

        setTimeout(function(){
            $(".builder_import button").html('Creating... <i class="fa fa-spinner fa-spin" style="font-size:20px"></i>').css("pointer-events", "none");
        }, 7000);

        var formData = new FormData($(".builder_import:visible")[0]);
        var ajax_url = builder.ajax_url;
        $.ajax({
            type: "POST",
            dataType: "json",
            url: ajax_url,
            data: formData,
            contentType: false,
            processData: false,
            success: function (resp) {
                $(".builder_import button").html('Import');

                console.log(resp);
                if (resp.code == 200) {
                    Swal.fire({
                        title: "Success!",
                        text: resp.message,
                        confirmButtonColor: '#000',
                        icon: "success"
                    });
                    window.location.href = redirect_url;
                }else if (resp.code == 201) {
                    exceed_plan_limit(resp.message)
                } else {
                    Swal.fire({
                        title: "Error!",
                        text: resp.message,
                        confirmButtonColor: '#000',
                        icon: "error"
                    });
                }
            },
            error: function () {
                $(".builder_import button").html('Import');
                Swal.fire({
                    title: "Error!",
                    text: "Please try again later",
                    confirmButtonColor: '#000',
                    icon: "error"
                });
            }
        });
    });




    function exceed_plan_limit(message){
        Swal.fire({
            title: "Error!",
            text: message,
            confirmButtonColor: '#f27474',
            confirmButtonText: 'Upgrade',
            showCancelButton: true,
            icon: "error"
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                window.location = '/plans/';
            } else if (result.isDenied) {
                swal.close();
            }
        });
    }


}); //End JS




jQuery(document).ready(function($){
    // Show the first tab and hide the rest
    $('#tabs-navv li:first-child').addClass('active');
    $('.tab-content').hide();
    $('.tab-content:first').show();
    jQuery(".cart-sidebar-custom.side-col").hide();
            jQuery("#checkout-fields").hide();
    // Click function
    $('#tabs-navv li a').click(function(){
        $('#tabs-navv li').removeClass('active');
        $(this).parent().addClass('active');
        $('.tab-content').hide();
        $("form.woocommerce-checkout").hide();
        
        var activeTab = $(this).attr('href');
        if(activeTab == '#billing_details'){
            jQuery(".cart-sidebar-custom.side-col").show();
            jQuery("#checkout-fields").show();
            if(jQuery("#billing_details_filled").children().length > 0){
                jQuery("#billing_details").css({
                    'display':'flex',
                    'flex-flow':'row wrap'
                });
                if(!jQuery("#billing_details").hasClass('detailsFilled')){
                    jQuery("#billing_details").addClass('detailsFilled');
                }
            }else{
                jQuery("#billing_details").removeAttr('style');
                jQuery("#billing_details").removeClass('detailsFilled');
            }
        }else{
            jQuery("#billing_details").removeAttr('style');
            jQuery(".cart-sidebar-custom.side-col").hide();
            jQuery("#checkout-fields").hide();
        }
        $(activeTab).fadeIn();
        return false;
    });

    $(".woocommerce-billing-fields__field-wrapper").append('<p class="form-row"><button type="button" class="button" onclick="gotopaymentStep()">Save and Continue</button></p>');
    jQuery("#checkout-fields").append( jQuery('.woocommerce-additional-fields').detach() );
});

function gotopaymentStep(){
    var has_error = false;
    jQuery(".woocommerce-checkout .billing-area p.validate-required").each(function(){
        var id = jQuery(this).attr('id');
        
        if(id!=""){
            var field= id.replace("_field", "");
            if(jQuery("#"+field).val() == ""){
                jQuery(this).addClass('woocommerce-invalid woocommerce-invalid-required-field');
                has_error = true;
            }
        }
    });

    if(has_error === true){
        jQuery('html, body').animate({
            scrollTop: jQuery("#customer_details").offset().top
        }, 2000);
    }else{
        var billing_details = '<div class"editbox"><button type="button" onclick="editbilling()" class="button">Edit</button></div>';
        jQuery("p.thwcfd-field-wrapper").each(function(){
            var id = jQuery(this).attr('id').replace("_field", "");
            billing_details += '<p><strong>'+jQuery(this).children('label').text().replace('*','').trim()+':</strong> '+jQuery("#"+id).val()+'</p>';
        });
        jQuery("#billing_details").addClass('detailsFilled');
        jQuery("#billing_details_filled").show();
        jQuery("#billing_details_filled").html(billing_details);
        jQuery(".col-1.billing-area").hide();
        jQuery("#billing_details").css({
            'display':'flex',
            'flex-flow':'row wrap'
        });
    }
}

function editbilling(){
    jQuery("#billing_details").removeClass('detailsFilled');
    jQuery("#billing_details_filled").hide();
    jQuery(".col-1.billing-area").show();
    jQuery("#billing_details").removeAttr('style');
}