function makeAjaxRequest(url, method, data, successCallback, errorCallback) {
    // Make the AJAX request
    $.ajax({
        url: url,
        type: method,
        data: data,
        success: successCallback,
        error: errorCallback,
    });
}
