<script>
  $(document).ready(function () {
    //Clicked Action 

$('#startUpForm').on('submit', function (e) {
  e.preventDefault();

  const form = $(this);
  const formData = new FormData(this);
  const submitBtn = form.find('.displayAction');
  submitBtn.prop('disabled', true).text('Submitting...');

  $.ajax({
    type: 'POST',
    url: 'backend/participant.php',
    data: formData,
    processData: false,
    contentType: false,
    success: function (res) {
      try {
        const response = JSON.parse(res);
        if (response.success) {
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: response.message,
            confirmButtonColor: '#198754',
          });
          form[0].reset();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: response.message || 'An unknown error occurred.',
            confirmButtonColor: '#dc3545',
          });
        }
      } catch (err) {
        console.error(err);
        Swal.fire({
          icon: 'error',
          title: 'Parse Error',
          text: 'Could not parse server response.',
        });
      }
    },
    error: function (xhr, status, error) {
      console.error(error);
      Swal.fire({
        icon: 'error',
        title: 'Request Failed',
        text: 'Something went wrong. Please try again.',
      });
    },
    complete: function () {
      submitBtn.prop('disabled', false).text('Submit');
    }
  });
});
</script>