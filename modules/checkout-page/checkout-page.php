<div class="checkout-page">
  <div class="container">
    <?php the_content()?>
  </div>
</div>


<script>
  document.addEventListener('DOMContentLoaded', function(){
    $('.woocommerce-billing-fields__field-wrapper .form-row').map(res=>{
      console.log(res)
      if(res.id!=='billing_state_field'){
        $(this).$('.form-row').addClass('input-field active')
      }
    })
  })
</script>