<script type="text/javascript">
  // instantiate and show the pivot grid
  new orb.pgridwidget(config).
     .render(document.getElementById('demo-pgrid'));
     
  var config = function() {
    return {
        width: 1110,
        height: 520,
    	dataSource: orb.demo.data,
    	dataHeadersLocation: 'columns',
        theme: 'blue',
        toolbar: {
            visible: true
        },
    	grandTotal: {
    		rowsvisible: true,
    		columnsvisible: true
    	},
    	subTotal: {
    		visible: true,
            collapsed: true
    	},
        fields: [
            {
                name: '0',
                caption: 'Order'
            },
            {
                name: '1',
                caption: 'State',
            },
            {
                name: '2',
                caption: 'Payment',
                sort: {
                    order: 'asc'
                }
            },
            {
                name: '3',
                caption: 'Price'
            },
            {
                name: '4',
                caption: 'Year',
                sort: {
                    order: 'asc'
                }
            },
            {
                name: '5',
                caption: 'Month',
				sort: {
                    order: 'asc'
                }
            },
			{
                name: '6',
                caption: 'Day'
            },
			{
                name: '7',
                caption: 'Kosar',
				dataSettings: {
                    aggregateFunc: 'Price',
                    formatFunc: function(value) {
                        return Number(value).toFixed(0);
                    }
                }
            },
			
        ],
        rows    : [ 'Month' ],
        columns : [ 'Year' ],
        data    : [ 'Price' ]

    };
};   
</script>

<div id="demo-pgrid"></div>
