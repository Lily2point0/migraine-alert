const testURL = '../data/test_2.json';

$(document).ready(function(){

	initGraph();
});

function loadMigraineData(url) {
	return $.ajax({
		url: url,
		dataType: 'json',
		crossDomain: true
	})
	.done(function() {
	    console.log( "success" );
	})
	.fail(function(e) {
	    console.log( "error", e );
	});
}

	var x_dim_accessor = function(d){return d.timestamp};
  	var y_dim_accessor = function(d){return d.sound};

function initGraph() {
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

	var sound_line = d3.svg.line()
	    .x(function(d) { return x(d.timestamp); })
	    .y(function(d) { return y(d.sound); });

	var temp_line = d3.svg.line()
	    .x(function(d) { return x(d.timestamp); })
	    .y(function(d) { return y(d.temp); });

	var light_line = d3.svg.line()
	    .x(function(d) { return x(d.timestamp); })
	    .y(function(d) { return y(d.light); });


	d3.json(testURL, function(data) {
		console.log(data);
	  var env_result = data.result.environment;
	  var properties = ["sound", "temp", "light"];
	  x.domain(d3.extent(env_result, function(d) {return d.timestamp; }));
	  y.domain(d3.extent(
	  	function(array, names){
          var res = [];
          array.forEach(function(item){
             names.forEach(function(name){
                res = res.concat(item[name]);
             });
          });
          return(res);
        }(env_result, properties)
	  ));

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
	    /*.append("text")
	      .attr("fill", "#000")
	      .attr("transform", "rotate(-90)")
	      .attr("y", 6)
	      .attr("dy", "0.71em")
	      .style("text-anchor", "end")
	      .text("Time");*/

	  g.append("g")
	      .attr("class", "axis axis--y")
	      .call(y_axis);
	    /*.append("text")
	      .attr("fill", "#000")
	      .attr("transform", "rotate(-90)")
	      .attr("y", 6)
	      .attr("dy", "0.71em")
	      .style("text-anchor", "end")
	      .text("Sensors");*/

	      console.log(env_result);

	  g.append("path")
	      .datum(env_result)
	      .attr("class", "line")
	      .attr("d", sound_line);

	  g.append("path")
	      .datum(env_result)
	      .attr("class", "line_temp")
	      .attr("d", temp_line);

	  g.append("path")
	      .datum(env_result)
	      .attr("class", "line_light")
	      .attr("d", light_line);
	});
}