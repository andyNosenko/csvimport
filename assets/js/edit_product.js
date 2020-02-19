// $(document).ready(function () {
//
//     $(document).ajaxError(function (event, jqxhr, settings, exception) {
//         alert("Triggered ajaxError handler.");
//     });
//
//     $('.edit').click(function () {
//         var button = $(this);
//         var id = button.data("product-id");
//
//         $.ajax({
//             url: Routing.generate('get_product_by_id', {id: id}),
//             type: 'GET',
//             dataType: 'json',
//             async: true,
//             success: function (data, status) {
//                 var modal = $('#exampleModalLong');
//                 modal.find('.modal-title').text('The ID is: ' + id);
//                 modal.find('.modal-body #productCode').val(data.product_code);
//                 modal.find('.modal-body #productName').val(data.product_name);
//                 modal.find('.modal-body #productDescription').val(data.product_description);
//                 modal.find('.modal-body #stock').val(data.stock);
//                 modal.find('.modal-body #cost').val(data.cost);
//                 modal.find('.modal-body #discontinued').val(data.discontinued);
//                 modal.find('.modal-body #category').val(data.category);
//
//                 $('.save').click(function () {
//                     var product = {
//                         'product_code': modal.find('.modal-body #productCode').val(),
//                         'product_name': modal.find('.modal-body #productName').val(),
//                         'product_description': modal.find('.modal-body #productDescription').val(),
//                         'stock': modal.find('.modal-body #stock').val(),
//                         'cost': modal.find('.modal-body #cost').val(),
//                         'discontinued': modal.find('.modal-body #discontinued').val(),
//                         'category': modal.find('.modal-body #category').val()
//                     };
//
//                     $.post(
//                         Routing.generate('save_product_by_id', {id: id}),
//                         product,
//                         function () {
//                             alert("Saved successfully!");
//                             location.reload();
//                         }
//                     );
//
//                     $('.close-window').click();
//                 });
//             }
//         });
//     });
// });

$(document).ready(function () {

    $(document).ajaxError(function (event, jqxhr, settings, exception) {
        alert("Triggered ajaxError handler.");
    });

    $('.edit').click(function () {
        var button = $(this);
        var id = button.data("product-id");
        $.ajax({
            url: Routing.generate('get_product_by_id', {id: id}),
            type: 'GET',
            dataType: 'json',
            async: true,
            success: function (data, status) {
                var modal = $('#exampleModal');
                modal.find('.modal-title').text('The ID is: ' + id);
                modal.find('.modal-body #edit_product_productCode').val(data.product_code);
                modal.find('.modal-body #edit_product_productName').val(data.product_name);
                modal.find('.modal-body #edit_product_productDescription').val(data.product_description);
                modal.find('.modal-body #edit_product_stock').val(data.stock);
                modal.find('.modal-body #edit_product_cost').val(data.cost);
                modal.find('.modal-body #edit_product_discontinued').val(data.discontinued);
                modal.find('.modal-body #edit_product_category').val(data.category);

                $('.save').click(function () {
                    var product = {
                        'product_code': modal.find('.modal-body #edit_product_productCode').val(),
                        'product_name': modal.find('.modal-body #edit_product_productName').val(),
                        'product_description': modal.find('.modal-body #edit_product_productDescription').val(),
                        'stock': modal.find('.modal-body #edit_product_stock').val(),
                        'cost': modal.find('.modal-body #edit_product_cost').val(),
                        'discontinued': modal.find('.modal-body #edit_product_discontinued').val(),
                        'category': modal.find('.modal-body #edit_product_category').val()
                    };

                    $.post(
                        Routing.generate('save_product_by_id', {id: id}),
                        product,
                        function () {
                            alert("Saved successfully!");
                            location.reload();
                        }
                    );

                    $('.close-window').click();
                });
            }
        });
    });
});