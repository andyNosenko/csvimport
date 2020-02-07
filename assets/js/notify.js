$.ajax({
    url: '/notifications',
    type: 'POST',
    dataType: 'json',
    async: true,
    success: function (data, status) {
        $.each(data, function (key, element) {
            $('div#ajax-results').append(element['notification']+'</br>')
        });

    }
});