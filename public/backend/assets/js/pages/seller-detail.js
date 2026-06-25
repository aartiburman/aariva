 
  // sales_analytic
  
  var options = {
    chart: {
      height: 328,
      type: "area",
  
      dropShadow: {
        enabled: true,
        opacity: 0.2,
        blur: 10,
        left: -7,
        top: 22,
      },
      toolbar: {
        show: false,
      },
    },
    colors: ["#47ad94", "#0F0F10"],
    dataLabels: {
      enabled: false,
    },
  
    stroke: {
      show: true,
      curve: "smooth",
      width: 2,
      lineCap: "square",
    },
    series: [
      {
        name: "Expenses",
        data: typeof chart_expense_data !== 'undefined' ? chart_expense_data : [
          16800, 16800, 15500, 17000, 14800, 15500, 19000, 16000, 15000, 17000,
          14000, 17000,
        ],
      },
      {
        name: "Income",
        data: typeof chart_income_data !== 'undefined' ? chart_income_data : [
          16500, 17500, 16200, 21500, 17300, 16000, 16000, 17000, 16000, 19000,
          18000, 19000,
        ],
      },
    ],
    labels: typeof chart_labels !== 'undefined' ? chart_labels : [
      "Jan",
      "Feb",
      "Mar",
      "Apr",
      "May",
      "Jun",
      "Jul",
      "Aug",
      "Sep",
      "Oct",
      "Nov",
      "Dec",
    ],
    xaxis: {
      axisBorder: {
        show: false,
      },
      axisTicks: {
        show: false,
      },
      crosshairs: {
        show: true,
      },
      labels: {
        offsetX: 0,
        offsetY: 5,
        style: {
          fontSize: "12px",
          cssClass: "apexcharts-xaxis-title",
        },
      },
    },
    yaxis: {
      labels: {
        formatter: function (value, index) {
          if (value >= 1000) {
            return (value / 1000).toFixed(1) + "K";
          }
          return value.toFixed(0);
        },
        offsetX: -15,
        offsetY: 0,
        style: {
          fontSize: "12px",
          cssClass: "apexcharts-yaxis-title",
        },
      },
    },
    grid: {
      borderColor: "#191e3a",
      strokeDashArray: 5,
      xaxis: {
        lines: {
          show: true,
        },
      },
      yaxis: {
        lines: {
          show: false,
        },
      },
      padding: {
        top: -50,
        right: 0,
        bottom: 0,
        left: 5,
      },
    },
    legend: {
      show: true,
      position: 'top',
      horizontalAlign: 'right',
    },
  
    fill: {
      type: "gradient",
      gradient: {
        type: "vertical",
        shadeIntensity: 1,
        inverseColors: !1,
        opacityFrom: 0.12,
        opacityTo: 0.1,
        stops: [100, 100],
      },
    },
    responsive: [
      {
        breakpoint: 575,
        options: {
          legend: {
            offsetY: -50,
          },
        },
      },
    ],
  };
  var chart_el = document.querySelector("#sales_analytic_seller");
  if (chart_el) {
    var chart = new ApexCharts(chart_el, options);
    chart.render();
  }
  
  

