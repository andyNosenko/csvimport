$(document).ready(function () {

    $(document).ajaxError(function (event, jqxhr, settings, exception) {
        alert("Triggered ajaxError handler.");
    });

    $('.edit').hover(function () {
        var button = $(this);
        var id = button.data("product-id");
        var modal = $('#exampleModal' + id);
        $.ajax({
            url: Routing.generate('edit_product', {id: id}),
            type: 'GET',
            dataType: 'html',
            async: true,
            success: function (data, status) {
                // alert(data);
                $('#edit-window').append(data);
            },
        });

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
            modal.remove();
        });
    });
});