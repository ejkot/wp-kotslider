// JQuery very simple slider
//  -- mainclass - class for UL block
// 	-- curitem - elemet index for show
//  -- infoblock - enable/disbale info
//  -- ejkot software developing -----------------------
// 
(function($, window, undefined){
$.fn.kotslider = function(options) {
  var options = jQuery.extend({
	mainclass : 'kotslider',
	curitem : 0,
	infoblock : false

  },options);

  
  
  var init=function (){
        var show=function() {
            ci=curitem;
            var lis=$(mul).find("li");
            lis.each(function(i,item){
                if (i==ci) {
                        $(item).css("display","block");
                    } else
                    {
                        $(item).css("display","none");
                    }
            });
            if (options.infoblock) {
                var infb=$(my).find(".kotslider-info");
                $(infb).html((curitem+1)+" / "+divscnt);
    
            }
        }
            
            
                var goright=function() {
                   if (curitem<(divscnt-1)) curitem++;
                   show(curitem,mul);
                   return false;
                }
                
                var goleft=function() {
                   if (curitem>0) curitem--;
                   show(curitem,mul);
                   return false;
                }
            
            var curitem=options.curitem;
               $(this).addClass(options.mainclass);
           
               if (options.infoblock) {
                    $(this).prepend('<div class="kotslider-info"></div>');
                    }
                var my=$(this);
                var mul=$(this).find("ul");
                var lis=$(mul).find("li");
                var divs=$(mul).find(".kotslider-embeded");
                var divscnt=$(divs).length;
                divs.each(function(i,item){
                    if (i!=0) {
                        $(item).append('<a class="kotslider-la" href="#"></a>');
                        var la=$(item).find(".kotslider-la");
                        la.on("click",goleft);
                    }
                    if (i<(divscnt-1)) {
                        $(item).append('<a class="kotslider-ra" href="#"></a>');
                        var ra=$(item).find(".kotslider-ra");
                        ra.on("click",goright);
                        $(item).on("click",goright);
                    }
                });
                if (tlen==1 && divscnt>1) {
                    $(document).keydown(function(event) {
                        if (event.keyCode==39) goright();
                        if (event.keyCode==37) goleft();
                        });
                    }
                
                show(curitem,mul);
            };
          
   var tlen=this.length;
   this.each(init);
  }
})(jQuery, window);


