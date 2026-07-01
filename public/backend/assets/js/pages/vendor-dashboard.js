document.addEventListener("DOMContentLoaded", function() {

    // Helper to get chart color based on theme
    function getChartColor(theme) {
        return theme === 'dark' ? '#C6A75E' : '#C6A75E';
    }

    // Chart instances
    var conversionsChart = null;
    var performanceChart = null;
    var worldMap = null;

    // Current Theme
    var currentTheme = document.body.getAttribute('data-pc-theme') || 'light';

    //
    // Conversions Chart
    //
    if (document.querySelector("#conversions")) {
        var conversionsSeries = parseFloat(document.querySelector("#conversions").getAttribute('data-series')) || 0;
        
        var options = {
            chart: {
                height: 292,
                type: 'radialBar',
                // Theme awareness
                theme: {
                    mode: currentTheme
                }
            },
            plotOptions: {
                radialBar: {
                    startAngle: -135,
                    endAngle: 135,
                    dataLabels: {
                        name: {
                            fontSize: '14px',
                            color: undefined,
                            offsetY: 100
                        },
                        value: {
                            offsetY: 55,
                            fontSize: '20px',
                            color: undefined,
                            formatter: function (val) {
                                return val + "%";
                            }
                        }
                    },
                    track: {
                        background: currentTheme === 'dark' ? "rgba(255,255,255, 0.1)" : "rgba(170,184,197, 0.2)",
                        margin: 0
                    },
                }
            },
            fill: {
                gradient: {
                    enabled: true,
                    shade: 'dark',
                    shadeIntensity: 0.2,
                    inverseColors: false,
                    opacityFrom: 1,
                    opacityTo: 1,
                    stops: [0, 50, 65, 91]
                },
            },
            stroke: {
                dashArray: 4
            },
            colors: [getChartColor(currentTheme)],
            series: [conversionsSeries],
            labels: ['Revenue Growth'],
            responsive: [{
                breakpoint: 380,
                options: {
                    chart: {
                        height: 180
                    }
                }
            }],
            grid: {
                padding: {
                    top: 0,
                    right: 0,
                    bottom: 0,
                    left: 0
                }
            }
        }

        conversionsChart = new ApexCharts(
            document.querySelector("#conversions"),
            options
        );

        conversionsChart.render();
    }

    //
    // Performance-chart
    //
    if (document.querySelector("#dash-performance-chart")) {
        var performanceData = [];
        var performanceMonths = [];
        var currency = document.querySelector("#dash-performance-chart").getAttribute('data-currency') || 'AED';
        
        try {
            performanceData = JSON.parse(document.querySelector("#dash-performance-chart").getAttribute('data-series'));
            performanceMonths = JSON.parse(document.querySelector("#dash-performance-chart").getAttribute('data-months'));
        } catch (e) {
            console.error("Error parsing performance data", e);
        }

        var performanceOptions = {
            series: [{
                name: "Revenue",
                type: "bar",
                data: performanceData,
            }],
            chart: {
                height: 313,
                type: "line",
                toolbar: {
                    show: false,
                },
                // Theme awareness
                theme: {
                    mode: currentTheme
                }
            },
            stroke: {
                dashArray: [0],
                width: [2],
                curve: 'smooth'
            },
            fill: {
                opacity: [1],
                type: ['solid'],
            },
            markers: {
                size: [0],
                strokeWidth: 2,
                hover: {
                    size: 4,
                },
            },
            xaxis: {
                categories: performanceMonths.length > 0 ? performanceMonths : [
                    "Jan", "Feb", "Mar", "Apr", "May", "Jun", 
                    "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
                ],
                axisTicks: {
                    show: false,
                },
                axisBorder: {
                    show: false,
                },
            },
            yaxis: {
                min: 0,
                axisBorder: {
                    show: false,
                },
                title: {
                    text: 'Revenue (' + currency + ')'
                }
            },
            grid: {
                show: true,
                strokeDashArray: 3,
                xaxis: {
                    lines: {
                        show: false,
                    },
                },
                yaxis: {
                    lines: {
                        show: true,
                    },
                },
                padding: {
                    top: 0,
                    right: -2,
                    bottom: 0,
                    left: 10,
                },
            },
            legend: {
                show: true,
                horizontalAlign: "center",
                offsetX: 0,
                offsetY: 5,
                markers: {
                    width: 9,
                    height: 9,
                    radius: 6,
                },
                itemMargin: {
                    horizontal: 10,
                    vertical: 0,
                },
            },
            plotOptions: {
                bar: {
                    columnWidth: "30%",
                    barHeight: "70%",
                    borderRadius: 3,
                },
            },
            colors: [getChartColor(currentTheme)],
            tooltip: {
                shared: true,
                y: [{
                    formatter: function (y) {
                        if (typeof y !== "undefined") {
                            return currency + " " + y;
                        }
                        return y;
                    },
                }],
            },
        }

        performanceChart = new ApexCharts(
            document.querySelector("#dash-performance-chart"),
            performanceOptions
        );

        performanceChart.render();

        // Filter functionality
        const filterContainer = document.querySelector("#performance-filter");
        if (filterContainer) {
            filterContainer.addEventListener("click", function(e) {
                const button = e.target.closest("button");
                if (!button) return;

                const filter = button.getAttribute("data-filter");
                if (!filter) return;

                // Update UI
                filterContainer.querySelectorAll("button").forEach(btn => {
                    btn.classList.remove("active", "btn-purple");
                    btn.classList.add("btn-outline-light", "text-dark");
                    btn.style.backgroundColor = "";
                    btn.style.color = "";
                });
                button.classList.add("active", "btn-purple");
                button.classList.remove("btn-outline-light", "text-dark");
                button.style.backgroundColor = "#5d1a8f";
                button.style.color = "white";

                // Fetch data
                const url = document.querySelector("#dash-performance-chart").getAttribute("data-url");
                fetch(`${url}?filter=${filter}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            performanceChart.updateOptions({
                                xaxis: {
                                    categories: data.labels
                                },
                                series: [{
                                    name: "Revenue",
                                    type: "bar",
                                    data: data.revenue,
                                }]
                            });
                        }
                    })
                    .catch(error => console.error("Error fetching performance data:", error));
            });
        }
    }

    //
    // World Map Markers
    //
    function initMap(theme) {
        if (document.querySelector("#world-map-markers")) {
            // Clear existing map if any (jsVectorMap appends to container)
            document.querySelector("#world-map-markers").innerHTML = '';

            var countryData = [];
            try {
                countryData = JSON.parse(document.querySelector("#world-map-markers").getAttribute('data-countries'));
            } catch (e) {
                console.error("Error parsing country data", e);
            }

            var mapSeries = {};
            countryData.forEach(function(item) {
                 if(item.code && item.code !== 'UNKNOWN') {
                      var count = typeof item.sessions === 'string' ? parseFloat(item.sessions.replace(/,/g, '')) : item.sessions;
                      mapSeries[item.code] = count;
                 }
            });
            
            var isDark = theme === 'dark';
            var mapBg = isDark ? '#2f374b' : '#cfd9e8';
            var scaleStart = isDark ? '#404a63' : '#e0cffc';
            var scaleEnd = getChartColor(theme);

            worldMap = new jsVectorMap({
                map: "world_merc",
                selector: "#world-map-markers",
                zoomOnScroll: true,
                zoomButtons: true,
                selectedRegions: ['NP'],
                focusOn: {
                    region: 'NP',
                    animate: true,
                    scale: 5
                },
                regionStyle: {
                    initial: {
                        fill: mapBg
                    },
                    hover: {
                        fill: scaleEnd
                    },
                    selected: {
                        fill: scaleEnd
                    }
                },
                visualizeData: {
                    scale: [scaleStart, scaleEnd],
                    values: mapSeries
                },
                onRegionTooltipShow(event, tooltip, code) {
                     var count = mapSeries[code] || 0;
                     tooltip.text(
                          tooltip.text() + ' (Orders: ' + count + ')'
                     )
                }
            });
        }
    }
    
    // Initial map render
    initMap(currentTheme);


    //
    // Theme Change Observer
    //
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'data-pc-theme') {
                var newTheme = document.body.getAttribute('data-pc-theme');
                updateCharts(newTheme);
            }
        });
    });

    observer.observe(document.body, {
        attributes: true
    });

    function updateCharts(theme) {
        var isDark = theme === 'dark';
        
        // Update Conversions Chart
        if (conversionsChart) {
            conversionsChart.updateOptions({
                theme: { mode: theme },
                colors: [getChartColor(theme)],
                plotOptions: {
                    radialBar: {
                        track: {
                            background: isDark ? "rgba(255,255,255, 0.1)" : "rgba(170,184,197, 0.2)"
                        }
                    }
                }
            });
        }

        // Update Performance Chart
        if (performanceChart) {
             performanceChart.updateOptions({
                theme: { mode: theme },
                colors: [getChartColor(theme)]
            });
        }

        // Update Map
        // Re-initialize map as styles are hard to update dynamically without full re-render
        initMap(theme);
    }

});
