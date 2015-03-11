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
	altblock : false
  },options);
 
   this.each(function(i){ 
		var my=jQuery(this).get(0);
		mul=$(my).find("ul");
		$(mul).find('.kotslider-embeded').append('<a class="kotslider-la" href="#"></a>');
		$(mul).find('.kotslider-embeded').append('<a class="kotslider-ra" href="#"></a>');
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
		//if (curh>0) $(mul).height(curh);

		var ra=$(my).find(".kotslider-ra");
		var la=$(my).find(".kotslider-la");
		if(options.infoblock) $("#"+options.infoblock).html(" "+(options.curitem+1)+" / "+itcnt);
		//var zz=$(mul).find("li:eq("+0+") ."+options.altblock).html();
		//console.log (zz);
		if ((options.altblock) && ($(mul).find("li:eq("+options.curitem+") ."+options.altblock).html()===undefined)) {$(mul).find("li:eq("+options.curitem+") img").after('<div class="'+options.altblock+'" id="'+options.altblock+'">'+$(mul).find("li:eq("+options.curitem+") img").attr('alt')+"<div>");}
		if (options.curitem==itcnt-1 && itcnt<2) $(ra).hide(); else $(ra).show();
		if (options.curitem==0) $(la).hide(); else $(la).show();
		$(my).on("click",".kotslider-ra, img",function(){
		if (options.curitem<itcnt-1) {
		$(mul).find("li:eq("+options.curitem+")").css("display","none");
			//	$(mul).find("li:eq("+options.curitem+")").fadeOut('fast');
			//	$(mul).find("li:eq("+options.curitem+")").css("position","absolute");
				options.curitem++;
				$(mul).find("li:eq("+options.curitem+")").css("display","block");
			//	$(mul).find("li:eq("+options.curitem+")").css("position","absolute");
			//	$(mul).height($(mul).find("li:eq("+options.curitem+")").height());
			//	$(mul).find("li:eq("+options.curitem+")").fadeIn('fast');
				};
		if (options.curitem==itcnt-1) $(ra).hide(); else $(ra).show();
		if (options.curitem==0) $(la).hide(); else $(la).show();
		if(options.infoblock) $("#"+options.infoblock).html(""+(options.curitem+1)+" / "+itcnt);
	    if (options.altblock) $("#"+options.altblock).html($(mul).find("li:eq("+options.curitem+") img").attr('alt'));
		return false;
		});
		$(la).on("click",function(){
		if (options.curitem>0) {
				$(mul).find("li:eq("+options.curitem+")").css("display","none");
				options.curitem--;
				//$(mul).height($(mul).find("li:eq("+options.curitem+")").height());
				$(mul).find("li:eq("+options.curitem+")").css("display","block");
				}
		if (options.curitem==itcnt-1) $(ra).hide(); else $(ra).show();
		if (options.curitem==0) $(la).hide(); else $(la).show();
		if(options.infoblock) $("#"+options.infoblock).html("Фото "+(options.curitem+1)+" из "+itcnt);
		if (options.altblock) $("#"+options.altblock).html($(mul).find("li:eq("+options.curitem+") img").attr('alt'));
		return false;
		});
		
		return false;
		});
  }
})(jQuery, window);