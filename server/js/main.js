const urlJWT = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJtYWNoaW5lX2lkIjoiMzM0ZjUzMGU3MThhMzZmOWYzZWE4Y2Q5NzJhMzhmOGEifQ.uvGLOudgzlZalOrqp7IZ3VTfW2ruNXSSYW6O2OmsPgY';

var apiData = new sgx.lib.rest("http://migrainealert.hopto.org/api/data.php");
var apiUser = new sgx.lib.rest("http://migrainealert.hopto.org/api/users.php");
var header = {'Auth': 'Bearer ' + urlJWT};
var params = {list:1, user:'5831d111251df', environment:'5831d1110db9e'};

apiData.getRequest(params, header).then(function(e) {
	initGraph(e.result.environment);
});


apiUser.getRequest({id:'5831d111251df'}, header).then(function(e) {
	$('#name').html(e.result.name);
});



var x_dim_accessor = function(d){return d.timestamp};
var y_dim_accessor = function(d){return d.sound};

function initGraph(env_result) {
	var svg = d3.select("#migraineGraph").append('svg').attr('width', 500).attr('height', 250),
    margin = {top: 0, right: 0, bottom: 0, left: 0},
    width = 500,
    height = 250,
    g = svg.append("g").attr("transform", "translate(" + margin.left + "," + margin.top + ")");

	var parseTime = d3.time.format("%d-%b-%y").parse;//not working

	var x = d3.time.scale()
	    .rangeRound([0, width]);

	var y = d3.scale.linear()
	    .rangeRound([height, 0]);

	var line = d3.svg.line()
	    .x(function(d) { return x(d.timestamp); })
	    .y(function(d) { return y(d.sound); });


	  x.domain(d3.extent(env_result, function(d) {return d.timestamp; }));
	  y.domain(d3.extent(env_result, function(d) { return d.sound; }));

	  var x_axis = d3.svg.axis()
	  	.orient('bottom')
	  	.scale(x);

	  var y_axis = d3.svg.axis()
	  	.orient('left')
	  	.scale(y);

	  g.append("g")
	      .attr("class", "axis axis--x")
	      .attr("transform", "translate(0," + height + ")")
	      .call(x_axis)
	    .append("text")
	      .attr("fill", "#000")
	      .attr("transform", "rotate(-90)")
	      .attr("y", 6)
	      .attr("dy", "0.71em")
	      .style("text-anchor", "end")
	      .text("Time");

	  g.append("g")
	      .attr("class", "axis axis--y")
	      .call(y_axis)
	    .append("text")
	      .attr("fill", "#000")
	      .attr("transform", "rotate(-90)")
	      .attr("y", 6)
	      .attr("dy", "0.71em")
	      .style("text-anchor", "end")
	      .text("Sound");

	      console.log(env_result);

	  g.append("path")
	      .datum(env_result)
	      .attr("class", "line")
	      .attr("d", line);
}
