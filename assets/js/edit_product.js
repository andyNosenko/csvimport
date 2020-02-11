$(document).ready(function () {
    $('.edit').click(function () {
        var  button = $(this);
        var id = button.data("whatever");

        $.ajax({
            url: '/product/'+id+'',
            type: 'POST',
            dataType: 'json',
            async: true,
            success: function (data, status) {
                var modal = $('#exampleModalLong');
                modal.find('.modal-title').text('The ID is: ' + id);
                modal.find('.modal-body #productCode').val(data.product_code);
                modal.find('.modal-body #productName').val(data.product_name);
                modal.find('.modal-body #productDescription').val(data.product_description);
                modal.find('.modal-body #stock').val(data.stock);
                modal.find('.modal-body #cost').val(data.cost);
                modal.find('.modal-body #discontinued').val(data.discontinued);
                modal.find('.modal-body #category').val(data.category);

                $('.save').click(function () {
                    var product = {
                        'product_code': modal.find('.modal-body #productCode').val(),
                        'product_name':  modal.find('.modal-body #productName').val(),
                        'product_description': modal.find('.modal-body #productDescription').val(),
                        'stock': modal.find('.modal-body #stock').val(),
                        'cost':  modal.find('.modal-body #cost').val(),
                        'discontinued': modal.find('.modal-body #discontinued').val(),
                        'category':  modal.find('.modal-body #category').val()
                    };

                    $.ajax({
                        url: '/save/'+id,
                        type: 'POST',
                        dataType: 'object',
                        data: product,
                        async: true,
                        success: function (data, status) {
                            alert(data)
                        },
                        error: function(error) {
                            location.reload();
                        }
                    });

                    $('.close-window').click();
                });
            }
        });
    });
});