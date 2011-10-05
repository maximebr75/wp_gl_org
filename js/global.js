jQuery(document).ready(function(){
    var $ = jQuery;
    var $total_width = 0;

    /* GESTION DE L'AFFICHAGE DE L'ORGANNIGRAMME */
    // R�cuperation des �l�ments cr�es
    $('#primaryNav > li').each(function() {
        var $this     = $(this);
        var $level    = $this.data('level');
        var $multiple = 0;
        var $width_fixed = 50;

        // Si nous avons un level superieure ou egal � 3
        // Alors on augmente le marge de l'�l�ment fr�re
        if($level >= 3) {
            $multiple = $level - 3;
            if($this.next().length > 0) {
                $this.next().css('marginLeft', ($multiple * $width_fixed) + 10);
                $total_width += ($multiple * $width_fixed) + 60 + 300;
            }
            else {
                $total_width +=  300;
            }
        }
        else {
            $total_width +=  300;
        }

    });
    $('#primaryNav').css('width', $total_width + 100);

    /* GESTION DE L'OUVERTURE AVEC COLORBOX */
    $('.span_elem').colorbox({
        html : function() {
            var $flux = $(this).next();
            return $flux.html();
        }
    });
});