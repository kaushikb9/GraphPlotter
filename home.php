<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Graph Plotter</title>
  <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" rel="stylesheet">
  <style>
  body {
    font: 10px sans-serif;
  margin-left:150px;margin-right:150px;
  }

  .axis path,
  .axis line {
    fill: none;
    stroke: #000;
    shape-rendering: crispEdges;
  }

  .x.axis path {
    display: none;
  }

  .line {
    fill: none;
    stroke: steelblue;
    stroke-width: 1.5px;
  }

  .grid .tick {
    stroke: lightgrey;
    opacity: 0.7;
  }
  .grid path {
      stroke-width: 0;
  }
  .grid .tick {
    stroke: lightgrey;
    opacity: 0.7;
  }
  .grid path {
      stroke-width: 0;
  }

  </style>
</head>
<body>

	<h2>Graph Plotter</h2>
	<hr />
  <div class="graph-container" style="width=960px;height=120px;">
  </div>

  <div class="equation-container" style="margin-left:150px;margin-right:150px;">
    <h3>Enter Equation</h1>
    <hr />
    <div class="equation-box"></div>
  </div>


  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.4.4/underscore-min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/backbone.js/1.0.0/backbone-min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/d3/3.0.8/d3.min.js"></script>

  <script type="text/template" id="graph-template">
    <%

    function make_x_axis() {        
      return d3.svg.axis()
        .scale(x)
         .orient("bottom")
         .ticks(10)
    }

    function make_y_axis() {        
      return d3.svg.axis()
        .scale(y)
        .orient("left")
        .ticks(10)
    }

   

    var margin = {top: 10, right: 20, bottom: 10, left: 50},
        width = 960 - margin.left - margin.right,
        height = 500 - margin.top - margin.bottom;

    var x = d3.scale.linear()
        .range([0, width]);

    var y = d3.scale.linear()
        .range([height, 0]);

    var xAxis = d3.svg.axis()
        .scale(x)
        .orient("bottom");

    var yAxis = d3.svg.axis()
        .scale(y)
        .orient("left");

    var line = d3.svg.line()
        .interpolate("basis")
        .x(function(d) { return x(d.x); })
        .y(function(d) { return y(d.y); });

    var svg = d3.select(".graph-container").append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
		.append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

      x.domain([-50,50]);
      maxy = d3.max(data, function(d) { return d.y; });
      y.domain([-maxy,maxy]);

      svg.append("path")
          .datum(data)
          .attr("class", "line")
          .attr("d", line);

      svg.append("g")
        .attr("class", "axis")
        .attr("transform", "translate(0," + (height/2) + ")")
        .call(xAxis);

      svg.append("g")
        .attr("class", "axis")
        .attr("transform", "translate(" + width/2 + ",0)")
        .call(yAxis);

      svg.append("g")         
        .attr("class", "grid")
        .attr("transform", "translate(0," + height + ")")
        .call(make_x_axis()
            .tickSize(-height, 0, 0)
            .tickFormat("")
        )

      svg.append("g")         
        .attr("class", "grid")
        .call(make_y_axis()
            .tickSize(-width, 0, 0)
            .tickFormat("")
        ) 
    %>
  </script>

  <script type="text/template" id="equation-template">
    <form class="edit-user-form">
    <button type="button" class="evaluate" style="float:right;margin-right:200px;">Evaluate</button>
        <label>Equation (example: 4x^3+3x^2+2x+1)</label>
        <input id="equation" type="text" value="4x^3+3x^2+2x+1">
        <label>Range of X-axis</label>
        <input id="range-min" type="number" value="-10" maxlength="4" size="4" style="width:70px;">
        <span style="padding:10px;"><big><big>to</big></big></span>
        <input id="range-max" type="number" value="10" maxlength="4" style="width:70px;">
        <hr />
       
    </form>

  </script>

  <script>

    var EquationView = Backbone.View.extend({
      el: '.equation-box',
      events: {
        'blur input': 'evaluate',
        'focus input': 'evaluate',
        'change input': 'evaluate',
        'click .evaluate': 'evaluate',
      },

      evaluate: function(){
        var equation = $('#equation').val();
        var rangeMin = $('#range-min').val();
        var rangeMax = $('#range-max').val();

		//replace '^' as power
		while(equation.indexOf('x^')!=-1){
			var oldPowerString = 'x^'+equation[equation.indexOf('x^')+2];
			var newPowerString = '';
			for(var i=0;i<equation[equation.indexOf('x^')+2]-1;i++){
				newPowerString = newPowerString+'x'+'*'
			}
			newPowerString = newPowerString+'x';
			equation = equation.replace(oldPowerString,newPowerString);
		}
		//append '*' to co-efficients 
		for(var i=0; i<equation.length;i++){
			if(equation[i] >= '0' && equation[i] <= '9'){
				if(equation[i+1]=='x'){
					equation = equation.slice(0,i+1)+'*'+equation.slice(i+1);
				}
			}
		};
		console.log("Reformatted Equation = "+equation);

        var data = [];

        for(var x=rangeMin;x<=rangeMax;x++){
          data.push({x:(parseInt(x)),y:eval(equation)});
        }
        console.log("Data in EquationView = "+JSON.stringify(data));
        var graphView = new GraphView();
        graphView.render({data:data});
      },

      render: function () {
        var template = _.template($('#equation-template').html());
        this.$el.html(template);
      }
    });

    var GraphView = Backbone.View.extend({
      el: '.graph-box',

      render: function (options) {
		    $('.graph-container').empty();
        console.log("Data in GraphView = "+JSON.stringify(options.data));
        var template = _.template($('#graph-template').html(), {data:options.data});
        this.$el.html(template);
      }
    });

    var equationView = new EquationView();
    equationView.render();
  </script>


</body>
</html> 