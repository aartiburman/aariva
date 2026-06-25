/**
 * Theme: Larkon - Responsive Bootstrap 5 Admin Dashboard
 * Author: Techzaa
 * Module/App: Dashboard
 */

//
// Conversions
// 
document.addEventListener("DOMContentLoaded", function() {

    // Helper to get chart color based on theme
    function getChartColor(theme) {
        return theme === 'dark' ? '#C6A75E' : '#4b0082';
    }

    // Chart instances
    var conversionsChart = null;
    var performanceChart = null;

    // Current Theme
    var currentTheme = document.body.getAttribute('data-pc-theme') || 'light';

    if (document.querySelector("#conversions")) {
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
            series: [parseFloat(document.querySelector("#conversions").getAttribute('data-series')) || 0],
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
        var currency = document.querySelector("#dash-performance-chart").getAttribute('data-currency') || '$';

        try {
            performanceData = JSON.parse(document.querySelector("#dash-performance-chart").getAttribute('data-series'));
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
                categories: [
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
                    text: 'Revenue'
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
    }

    // Monitor theme changes
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === "attributes" && mutation.attributeName === "data-pc-theme") {
                var theme = document.body.getAttribute('data-pc-theme') || 'light';
                
                if (conversionsChart) {
                    conversionsChart.updateOptions({
                        theme: { mode: theme },
                        colors: [getChartColor(theme)],
                        plotOptions: {
                            radialBar: {
                                track: {
                                    background: theme === 'dark' ? "rgba(255,255,255, 0.1)" : "rgba(170,184,197, 0.2)"
                                }
                            }
                        }
                    });
                }

                if (performanceChart) {
                    performanceChart.updateOptions({
                        theme: { mode: theme },
                        colors: [getChartColor(theme)]
                    });
                }
                
                // Note: jsVectorMap doesn't support dynamic theme update easily without re-init,
                // but for now we focus on ApexCharts as requested.
            }
        });
    });

    observer.observe(document.body, {
        attributes: true,
        attributeFilter: ['data-pc-theme']
    });




    //
    // Vector Map
    //
    if (document.querySelector('#world-map-markers')) {
        const map = new jsVectorMap({
            map: 'world',
            selector: '#world-map-markers',
            zoomOnScroll: true,
            zoomButtons: false,
            markersSelectable: true,
            markers: [
                { name: "Nepal", coords: [28.3949, 84.1240] },
            ],
            markerStyle: {
                initial: { fill: "#7f56da" },
                selected: { fill: "#22c55e" }
            },
            labels: {
                markers: {
                    render: marker => marker.name
                }
            },
            regionStyle: {
                initial: {
                    fill: 'rgba(169,183,197, 0.3)',
                    fillOpacity: 1,
                },
            },
        });
    }

});
