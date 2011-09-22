jQuery(document).ready( function(){

    /* Toggle functions -> */
    //Hide (Collapse) the toggle containers on load
    jQuery(".arconix-toggle-content").hide();

    //Switch the "Open" and "Close" state per click
    jQuery(".arconix-toggle-title").toggle(function(){
	    jQuery(this).addClass("active");
	    }, function () {
	    jQuery(this).removeClass("active");
    });
    //Slide up and down on click
    jQuery(".arconix-toggle-title").click(function(){
	    jQuery(this).next(".arconix-toggle-content").slideToggle();
    });

    //Tabs
    jQuery("ul.arconix-tabs").tabs("div.arconix-panes > div");

    //Accordion
    jQuery(".arconix-accordions-vertical").tabs(".arconix-accordions-vertical div.arconix-accordion-content", {tabs: 'h3', effect: 'slide', initialIndex: 0 });
});