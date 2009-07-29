google.load("visualization", "1", {packages:["piechart", "geomap", "linechart", "areachart"]});

function doareachart( data, options, id ) {
	var chart = new google.visualization.AreaChart( document.getElementById( id ) );
	chart.draw( data, options );
}
	
function dolinechart( data, options, id ) {
	var chart = new google.visualization.LineChart( document.getElementById( id ) );
	chart.draw( data, options );
}
	
function dopiechart( data, options, id ) {
	var chart = new google.visualization.PieChart( document.getElementById( id ) );
	chart.draw( data, options );
}
	
function dogeomap( data, options, id ) {
	var chart = new google.visualization.GeoMap( document.getElementById( id ) );
	chart.draw( data, options );
}
