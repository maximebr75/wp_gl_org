jQuery(document).ready(function(){

    var $ = jQuery;
    var $button  = $('input.thickbox').clone();
    var $elem_current;
    var $button_del = '<input type="button" class="del" name="Supprimer" value="Supprimer" />';
    var $data = [];
    var width = 0;

    display_org();

    // Si nous avons un organigramme lors de la modification, alors on affiche les boutons pour chaque élément
    if($('#view_org #primaryNav').length > 0) {
        $('.span_elem').each(function() {
            $(this).append($button.clone(), $button_del);
        });
        $('#choice_type_elem').show();
        $('#empty_org').hide();
    }

    // Gestion de l'ajout d'un élément dans l'organigramme
    $('#add_elem').live('click', function() {
        // On vérifie que le titre est au moins présent
        var $inside        = $(this).closest('.inside')
        var $val_elem_name = $inside.find('.elem_name').val();

        if($val_elem_name.length === 0) {
            $('#elem_error').show();
            return false;
        }

        // On vérifie à quel niveau on ajoute l'élément.
        // Si il n'y a aucun élément alors on admet que nous sommes au niveau 1
        if($('#primaryNav').length === 0) {
            var $new_elem = $('<ul id="primaryNav" class="col3"><li id="create_elem" data-level="0" class="first_li max_level"><span class="span_elem">'+$val_elem_name+'</span></li></ul>');
            $('#view_org').prepend($new_elem);
        }
        // Dans le cas ou le premier niveau est déjà défini
        else {
            var $type_elem = $('input[name="type_elem"]:checked').val();
            if($type_elem == 'enfant') {
                // Si il existe déjà un enfant, on ne recrée pas d'ul
                if($elem_current.children('ul').length === 0) {
                    var $new_elem = $('<ul style="height:auto" class="clearfix"><li id="create_elem"><span class="span_elem">'+$val_elem_name+'</span></li></ul>');
                    $elem_current.append($new_elem); // Creation d'un élément enfant

                }else {
                    var $new_elem = $('<li id="create_elem"><span class="span_elem">'+$val_elem_name+'</span></li>');
                    $elem_current.children('ul').append($new_elem);
                }

                // Gestion des levels
                if($new_elem.closest('li').attr('class') == 'first_li max_level' || $new_elem.closest('li').attr('class') == 'max_level') {
                    $new_elem.attr('class', 'clearfix level-1');
                }
                else {
                    var $level = $elem_current.closest('ul').attr('class');
                    if(typeof($level) != 'undefined') {
                        var $new_level = 0;
                        $level = $level.replace('clearfix level-', '');
                        $new_level = parseInt($level) + 1;

                        $new_elem.attr('class', 'clearfix level-'+$new_level);

                        // Gestion du data du premier li
                        if($new_elem.closest('.max_level').data('level') < $new_level) {
                            $new_elem.closest('.max_level').attr('data-level', $new_level);
                            $new_elem.closest('.max_level').data('level', $new_level);
                            display_org($new_level);
                        }
                    }
                }
            }
            else {
                var $new_elem = $('<li id="create_elem"><span class="span_elem">'+$val_elem_name+'</span></li>')
                $elem_current.after($new_elem); // Création d'un élément frère

                // Dans le cas ou nous sommes en présence du premier "li" apres le primaryNav
                if($elem_current.attr('class') == 'max_level' || $elem_current.attr('class') == 'first_li max_level') {
                    $new_elem.attr('data-level', 0);
                    $new_elem.attr('class', 'max_level');
                }
            }
        }

        $('#create_elem span').append($button.clone(), $button_del).removeAttr('id');
        // On ferme la fenêtre de création et on purge les données contenu dans les champs input
        $("#TB_closeWindowButton").trigger('click');
        $('.field_org input[type="text"]').val('');
        $('#choice_type_elem').show();
        $('#empty_org').hide(); // On retire l'information votre organigramme et vide...
        display_org();
    });

    // Gestion du bouton d'annulation
    $('#del_add').click(function() {
        $("#TB_closeWindowButton").trigger('click');
    });

    // Gestion du click sur le bouton ajouter un élément
    $('.thickbox').live('click', function() {
        $elem_current = $(this).closest('li');
    });

    // Gestion de la suppression
    $('.del').live('click', function() {
        // Si il n'y a qu'un seul élément li, on le supprime tout simplement
        var $del_elem_current = $(this).closest('li');

        // Cas ou nous avons aucun frère ni aucun enfants
        if($del_elem_current.next().length === 0 && $del_elem_current.prev().length === 0 && $del_elem_current.children('ul').length === 0) {

            var $level = $del_elem_current.closest('ul').attr('class');
            if(typeof($level) != 'undefined') {
                $level = $level.replace('clearfix level-', '');
                if($del_elem_current.closest('.max_level').data('level') == $level && $del_elem_current.closest('.max_level .level-'+$level).length == 1) {
                    $del_elem_current.closest('.max_level').attr('data-level', parseInt($level) - 1);
                    $del_elem_current.closest('.max_level').data('level', parseInt($level) - 1);
                    display_org(parseInt($level) - 1);
                }
            }

            $del_elem_current.closest('ul').remove();
        }
        // Cas ou nous avons aucun enfant mais ou nous avons des frères
        else if( ($del_elem_current.next().length != 0 || $del_elem_current.prev().length != 0) && $del_elem_current.children('ul').length === 0){
            $del_elem_current.remove();
        }

        if($('li.max_level').length === 0) {
            $('#empty_org').show();
        }
        display_org();
    });

    // Gestion de la modification
    $('.view_actions').live('click', function() {
        $('.thickbox').toggle();
        $('.del').toggle();
    });

    // Gestion de la soumission du formulaire
    $('#publish').live('click', function() {
        $('.thickbox').remove();
        $('.del').remove();
        $('.max_level').css('marginLeft', '');
        $('#org_structure').val($('#view_org').html());
    });

    /* GESTION DE L'AFFICHAGE DE L'ORGANNIGRAMME */
    // Récuperation des éléments crées
    function display_org($level) {

        var $total_width = 0;

        $('#primaryNav > li').each(function() {
            var $this     = $(this);
            var $multiple = 0;
            var $width_fixed = 50;

            if(typeof($level) == 'undefined') {
                var $level = $this.data('level');
            }

            // Si nous avons un level superieure ou egal à 3
            // Alors on augmente le marge de l'élément frère
            if($level >= 3) {
                $multiple = $level - 3;
                $this.next('li').css('marginLeft', ($multiple * $width_fixed) + 30);
                $total_width += ($multiple * $width_fixed) + 60 + 300;
            } else {
                $this.next('li').css('marginLeft', 0);
                $total_width += 300;
            }
        });

        $('#primaryNav').css('width', $total_width + 100);
    }
});