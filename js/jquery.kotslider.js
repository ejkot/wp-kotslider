// JQuery very simple slider
//  -- mainclass - class for UL block
// 	-- curitem - elemet index for show
//  -- infoblock - #id for information div. false - no show information
//  -- ejkot software developing -----------------------
// 
(function($, window, undefined){
$.fn.kotslider = function(options) {
  var options = jQuery.extend({
	mainclass : 'kotslider',
	curitem : 0,
	infoblock : false,
	altblock : false,
	width: 0,
	height: 0
  },options);
 
   this.each(function(i){ 
		var sizex=$(window).width();
		var my=jQuery(this).get(0);
		mul=$(my).find("ul");
		maxh=0;
		itcnt=0;
		$(mul).find("li").each(function(idx,elem){
		var hh=$(elem).height();
		if (hh>maxh) maxh=hh;
		itcnt++;
		if (idx==options.curitem) $(elem).css("display","block");
		});
		$(my).css("position","relative");
		$(mul).addClass(options.mainclass);
		var curh=$(mul).find("li:eq("+options.curitem+") img").prop('offsetHeight');
				if (options.height) $(mul).find("li img").css("max-height",options.height);
				if (options.width) $(mul).find("li img").css("max-width",options.width);
				if (options.height && sizex>options.width) $(my).height(options.height);
				//if (options.width) $(my).width(options.width); 

		if (curh>0) $(mul).height(curh);
		$(mul).before('<a class="kotslider-la" href="#"></a>');
		$(mul).before('<a class="kotslider-ra" href="#"></a>');
		var ra=$(my).find(".kotslider-ra");
		var la=$(my).find(".kotslider-la");
		if(options.infoblock) $("#"+options.infoblock).html("Фото "+(options.curitem+1)+" из "+itcnt);
		if (options.altblock) $("#"+options.altblock).html($(mul).find("li:eq("+options.curitem+") img").attr('alt'));
		if (options.curitem==itcnt-1 && itcnt<2) $(ra).hide(); else $(ra).show();
		if (options.curitem==0) $(la).hide(); else $(la).show();
		$(window).on("resize",function(){
		var curh=$(mul).find("li:eq("+options.curitem+") img").prop('offsetHeight');
			if (curh>0) $(mul).height(curh);
			});
		
		var slideright=(function(){
		if (options.curitem<itcnt-1) {
				$(mul).find("li:eq("+options.curitem+")").fadeOut('fast');
				options.curitem++;
				$(mul).height($(mul).find("li:eq("+options.curitem+")").height());
				$(mul).find("li:eq("+options.curitem+")").fadeIn('fast');
				};
		if (options.curitem==itcnt-1) $(ra).hide(); else $(ra).show();
		if (options.curitem==0) $(la).hide(); else $(la).show();
		if(options.infoblock) $("#"+options.infoblock).html("Фото "+(options.curitem+1)+" из "+itcnt);
	    if (options.altblock) $("#"+options.altblock).html($(mul).find("li:eq("+options.curitem+") img").attr('alt'));
		return false;
		});
		
		var slideleft=(function(){
		if (options.curitem>0) {
				$(mul).find("li:eq("+options.curitem+")").css("display","none");
				options.curitem--;
				$(mul).height($(mul).find("li:eq("+options.curitem+")").height());
				$(mul).find("li:eq("+options.curitem+")").css("display","block");
				}
		if (options.curitem==itcnt-1) $(ra).hide(); else $(ra).show();
		if (options.curitem==0) $(la).hide(); else $(la).show();
		if(options.infoblock) $("#"+options.infoblock).html("Фото "+(options.curitem+1)+" из "+itcnt);
		if (options.altblock) $("#"+options.altblock).html($(mul).find("li:eq("+options.curitem+") img").attr('alt'));
		return false;
		});
		
		$(my).on('swiperight',function(e) {$(la).trigger('click');});
		$(ra).on("click",slideright);
		$(my).on("swipeleft",function(e) {$(ra).trigger('click');});
		$(la).on("click",slideleft);
		
		$(my).on('movestart', function(e) {
					if ((e.distX > e.distY && e.distX < -e.distY) ||
					    (e.distX < e.distY && e.distX > -e.distY)) {
						e.preventDefault();
						return;
					}
					//wrap.addClass('notransition');
				})
		
		
		return false;
		});
  }
})(jQuery, window);