$(document).ready(function() {
    $(".login-button").click(function () {
      $(".login-form").toggle("clip", {}, "fast");
	  $(".login-button a").toggleClass("active");
		return false;
    });
});