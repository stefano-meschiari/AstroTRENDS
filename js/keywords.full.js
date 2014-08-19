_.clone = function(obj) {
    if (obj === undefined)
        return obj;
    return JSON.parse(JSON.stringify(obj));
};

_.parameter = function(name) {
    return(decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null);
};



// Logic
var DB = (function() {
    var module = {};
    
    var database;
    var cache = {};
    // Template URL
    var adsUrl = "http://adsabs.harvard.edu/cgi-bin/nph-abs_connect?db_key=AST&db_key=AST&qform=AST&arxiv_sel=astro-ph&arxiv_sel=cond-mat&arxiv_sel=cs&arxiv_sel=gr-qc&arxiv_sel=hep-ex&arxiv_sel=hep-lat&arxiv_sel=hep-ph&arxiv_sel=hep-th&arxiv_sel=math&arxiv_sel=math-ph&arxiv_sel=nlin&arxiv_sel=nucl-ex&arxiv_sel=nucl-th&arxiv_sel=physics&arxiv_sel=quant-ph&arxiv_sel=q-bio&sim_query=YES&ned_query=YES&adsobj_query=YES&aut_logic=OR&obj_logic=OR&author=&object=&start_mon=&start_year=<%= year1 %>&end_mon=&end_year=<%= year2 %>&ttl_logic=OR&title=&txt_logic=OR&text=<%= keyword %>&nr_to_return=500&start_nr=1&jou_pick=NO&ref_stems=&data_and=ALL&group_and=ALL&start_entry_day=&start_entry_mon=&start_entry_year=&end_entry_day=&end_entry_mon=&end_entry_year=&min_score=&sort=SCORE&data_type=SHORT&aut_syn=YES&ttl_syn=YES&txt_syn=YES&aut_wt=1.0&obj_wt=1.0&ttl_wt=0.3&txt_wt=3.0&aut_wgt=YES&obj_wgt=YES&ttl_wgt=YES&txt_wgt=YES&ttl_sco=YES&txt_sco=YES&version=1";
    
    function countResults(keyword) {

        if (! cache[keyword]) {
            var k = _.keys(database[keyword]);
            cache[keyword] = _.map(k, function(k) {
                var year = +k;
                var url = _.template(adsUrl, {
                    year1: year,
                    year2: year,
                    keyword: keyword
                });
                return {x:year, y:+database[keyword][k], url:url};
            });
        }
        return cache[keyword];
    }

    function setDatabase(db) {
        database = db;
    }
    module.countResults = countResults;
    module.setDatabase = setDatabase;
    return module;
})();


// User interface part
var ASTRO_TRENDS_UI = (function() {
    var module = {};
    var nkeywords = 5;
    var plotter;
    var baseURL = "http://www.stefanom.org/playpen/Trends/keywords/index.php";
    
    var colors = ['rgb(92, 184, 92)', 'rgb(240, 173, 78)', 'rgb(217, 83, 79)', 'rgb(91, 192, 222)', '#428bca'];
    var defaultKeywords = [];
    var uiKeywords = [];
    var uiToDefault = {};
    var data = [];
    var noKeyword = "< No keyword >";
    
    function share() {
        var text = baseURL + "?year1=" + $("#year1").val() + "&year2=" + $("#year2").val();
        for (var i = 1; i < nkeywords; i++) {
            var val = $("#keyword" + i).val().trim();
            if (val != "" && val.indexOf("<") < 0)
                text += "&keyword" + i + "=" + encodeURI($("#keyword" + i).val());
        }

        if ($("#show-all").is(":checked"))
            text += "&show-all=true";
        if (normalizeState != 0)
            text += "&normalize=" + normalizeState;
        if (logOn)
            text += "&log=true";
        $("#share").val(text);
    }
    
    function sync() {

        $("#plot").highcharts().xAxis[0].setExtremes(+$("#year1").val()|0,
                                                     +$("#year2").val()|0);

        
        var plotter = $("#plot").highcharts();
        for (var i = 1; i < nkeywords; i++) {

            var val = $("#keyword" + i).select2('val');
            if (val.indexOf("<") == 0) {
                plotter.series[i-1].hide();
                plotter.series[i-1].name = "";
                $("#keyword" + i + "-prog").html("");
                $("#keyword" + i + "-badge").hide();
            } else {
                plotter.series[i-1].show();
                plotter.series[i-1].name = val;
                $("#keyword" + i + "-prog").html(val);
                $("#keyword" + i + "-badge").show();
            }
        };

        if ($("#show-all").is(":checked")) {
            $("#keyword5-prog").html("Total count");
            plotter.series[4].name = "Total count";
            plotter.series[4].show();
        } else {
            $("#keyword5-prog").html("");
            plotter.series[4].hide();
        }
        share();
        render();
    }

    function sampleKeywords(num) {
        num = num || 4;

        var initKeywords = _.sample(_.rest(uiKeywords), num);
        var i;

        for (i = 1; i <= num; i++) {
            $("#keyword" + i).select2("val", initKeywords[i-1]);
        }
        sync();
    }
    

    function render() {
        var plotter = $("#plot").highcharts();
        var kw = keywords();
        for (var i = 1; i <= nkeywords; i++) {
            var sel = null;
            var val = $("#keyword" + i).select2('val');

            if (i == nkeywords)
                sel = '"*"';
            else {
                if (!val || val.indexOf("<") == 0)
                    continue;

                sel = uiToDefault[val];
            }

            if (sel === null)
                continue;
            
            var counts = DB.countResults(sel);
            plotter.series[i-1].setData(_.clone(counts), true);
            data[i-1] = counts;
            $("#keyword" + i + "-badge").html(
                _.reduce(counts, function(memo, c) { return memo + c.y; }, 0)
            );


        }
        
        if (normalizeState != 0)
            normalize(normalizeState);
    }

    function keywords() {
        return _.map(_.range(1, nkeywords+1), function(i) {
            return $("#keyword" + i).val();
        });
    }

    function progress(i, v) {
        v = Math.min(20, Math.ceil(20*v));
        $("#keyword" + i + "-prog").css("width", v + "%");
    }


    var logOn = false;
    var normalizeState = 0;

    function log() {
        var plotter = $("#plot").highcharts();
        
        if (logOn) 
            plotter.yAxis[0].update({type: "logarithmic", min:null});
        else {
            plotter.yAxis[0].update({type: "linear", min:0});
        }

        normalize(normalizeState);
        share();
    }

    function normalize(num) {
        normalizeState = num;

        if (num == 0) {
            render();
            return;
        }
        var plotter = $("#plot").highcharts();
        
        var data0 = _.clone(data[num-1]);
        for (var i = nkeywords-1; i >= 0; i--) {
            var data2 = _.clone(data[i]);
            var newData = [];
            if (data2) {
                
                for (var j = 0; j < data2.length; j++) {
                    if (data0[j].y > 0) {
                        data2[j].y /= data0[j].y;
                        newData.push(data2[j]);
                    }
                }
                plotter.series[i].setData(newData, true);
            }
        };

        share();
    }


    function toggleLog() {
        logOn = !logOn;
        log();
    }

    function pointClick() {
        window.open(this.options.url, "_blank");
    }

    function init() {
        
        // Read keywords
        $.ajax({
            dataType:"json",
            url: "data/keywords.json",
            settings: {async: false},
            success: function(data) {
                DB.setDatabase(data);
                defaultKeywords = _.initial(_.keys(data));
                defaultKeywords.unshift(noKeyword);
                uiKeywords = _.map(defaultKeywords, function(str) {
                    var ret = str.replace(/\"/g, '');
                    uiToDefault[ret] = str;
                    return ret;
                });
                uiKeywords.sort();
                initUI();
            }
        });
    }
    
    function initUI() {
        
        // Initialize plot
        
        $("#plot").highcharts({
            chart: {
                type: 'line'
            },
            title: { text: 'Keyword popularity' },           
            xAxis: {
                title: { text: 'Years' },
                allowDecimals:false
            },
            yAxis: {
                title: { text: 'Number of articles'},
                min:0,
                type: 'linear'
            },
            series: [
                {
                    color: colors[0],
                    name: "",
                    cursor:'pointer',
                    point: {
                        events: {
                            click: pointClick
                        }
                    }    
                },
                {
                    color: colors[1],
                    name: "",
                    cursor:'pointer',
                    point: {
                        events: {
                            click: pointClick
                        }
                    }
                },
                {
                    color: colors[2],
                    name: "",
                    cursor:'pointer',
                    point: {
                        events: {
                            click: pointClick
                        }
                    }
                },
                {
                    color: colors[3],
                    name: "",
                    cursor:'pointer',
                    point: {
                        events: {
                            click: pointClick
                        }
                    }
                },
                {
                    color: colors[4],
                    name: "",
                    cursor:'pointer',
                    point: {
                        events: {
                            click: pointClick
                        }
                    }
                }
                
            ],
            legend: {
                enabled:false
            },
            tooltip: {
                formatter: function() {
                    
                    var val = this.y.toPrecision(5);

                    if ((+val) === (+val|0))
                        val = val|0;
                    var name = this.series.name;
                    if (name.length > 50)
                        name = name.substring(0, 50) + "...";
                    
                    return (this.x|0) + "<br><span style=\"color:" + this.series.color + "\">"+ name + "</span>: <b>" + val + "</b><br/>" + "Click to see the ADS page.<br>";
                }

            }
        });

        $("#inspire").on("click", function() { sampleKeywords(); });
        $("#suggest").on("click", function() {
            window.location = "mailto:stefano@astro.as.utexas.edu?subject=" + encodeURI("Hey, you forgot this keyword...");
        });
        $("#log").on("click", toggleLog);

        $("#share").on("click", function() {
            $(this).select();
        });
        
        var i;
        
        var optionsHtml = _.reduce(uiKeywords,
                                   function(memo, key) {
                                       return memo + "<option>" + key + "</option>";
                                   }, "");

        for (i = 1; i <= nkeywords; i++) {
            $("label[for=keyword" + i + "]").css("border-bottom", "4px solid " + colors[i-1]);
        }
        
        for (i = 1; i < nkeywords; i++) {
            $("#keyword" + i).append(optionsHtml);
            $("#keyword" + i).attr('selectedIndex', -1);
            $("#keyword" + i).select2();
            $("#keyword" + i).on("change", sync);
            $("#keyword" + i + "-close").click(function(i) {
                return function() {
                    $('#keyword' + i).select2('val', noKeyword);
                    sync();
                    return false;
                };
            }(i));

        }
        
        $("label[for=show-all]").css("border-bottom", "4px solid " + colors[4]);
        
        $("#show-all").on("click", sync);
        
        $("#year1").on("input blur", sync);
        if (_.parameter('keyword1')) {
            for (i = 1; i <= nkeywords; i++)
                $("#keyword" + i).select2('val', _.parameter('keyword' + i) || noKeyword);
            $("#year1").val(_.parameter('year1') || "1970");
            $("#year2").val(_.parameter('year2') || "2013");
            if (_.parameter('show-all'))
                $("#show-all").attr('checked', true);
            if (_.parameter('normalize'))
                normalizeState = +_.parameter('normalize')||0;
            if (_.parameter('log')) {
                logOn = true;
                log();
            }
            sync();
        } else {
            sampleKeywords(3);
        }
    };

    module.init = _.once(init);
    module.normalize = normalize;
    return module;
})();

$(document).ready(ASTRO_TRENDS_UI.init);
