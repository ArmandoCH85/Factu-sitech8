@php
    $configurationModel = \App\Models\Tenant\Configuration::first();
    $defaultImage = $configurationModel->product_default_image ?? 'imagen-no-disponible.jpg';
    $defaultImagePath = $defaultImage === 'imagen-no-disponible.jpg'
        ? asset('logo/imagen-no-disponible.jpg')
        : asset('storage/defaults/' . $defaultImage);
@endphp

<div class="dropdown cart-dropdown">
    <a href="#" class="dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-display="static">

		<svg clip-rule="evenodd" fill-rule="evenodd" height="24" stroke-linejoin="round" stroke-miterlimit="2" viewBox="0 0 512 512" width="24" xmlns="http://www.w3.org/2000/svg" id="fi_4893746"><path d="m211.892 383.468c24.344 0 44.108 19.764 44.108 44.108s-19.764 44.108-44.108 44.108-44.108-19.764-44.108-44.108 19.764-44.108 44.108-44.108zm176.22 0c24.344 0 44.108 19.764 44.108 44.108s-19.764 44.108-44.108 44.108-44.108-19.764-44.108-44.108 19.764-44.108 44.108-44.108zm-288.464-273.226s63.534 222.705 63.534 222.705c6.591 23.103 27.703 39.034 51.727 39.034h157.478c33.502 0 61.98-24.47 67.023-57.59 4.821-31.664 11.838-77.75 17.065-112.081 2.869-18.84-2.626-37.994-15.046-52.449-12.42-14.454-30.529-22.769-49.586-22.769h-235.394l-8.72-30.567c-7.633-26.757-32.085-45.209-59.91-45.209-23.033 0-51.825 0-51.825 0-13.798 0-25 11.202-25 25s11.202 25 25 25h51.825c5.494 0 10.321 3.643 11.829 8.926zm71.066 66.85h221.129c4.482 0 8.741 1.956 11.663 5.355 2.921 3.4 4.213 7.905 3.539 12.337 0 0-17.066 112.081-17.066 112.081-1.323 8.693-8.798 15.116-17.592 15.116h-157.478c-1.693 0-3.181-1.122-3.645-2.751 0 0-40.55-142.138-40.55-142.138z"></path></svg>

        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-shopping-bag bag-icon-restaurant"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M6.331 8h11.339a2 2 0 0 1 1.977 2.304l-1.255 8.152a3 3 0 0 1 -2.966 2.544h-6.852a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304z"></path><path d="M9 11v-5a3 3 0 0 1 6 0v5"></path></svg>
		<span class="cart-count" style="display:none;">0</span>
    </a>
    <div class="dropdown-menu px-4 ml-4">
        <div class="dropdownmenu-wrapper">

            <div class="dropdown-cart-products">

            </div><!-- End .cart-product -->

            <div class="dropdown-cart-total">
                <span>Total</span>

                <span class="cart-total-price">S/ 0</span>
            </div><!-- End .dropdown-cart-total -->

            <div class="dropdown-cart-action">
                <a  href="{{ route('tenant_detail_cart') }}" class="btn">Ver Carrito</a>
                <!--<a href="#" class="btn">Checkout</a> -->
            </div><!-- End .dropdown-cart-total -->
        </div><!-- End .dropdownmenu-wrapper -->
    </div><!-- End .dropdown-menu -->
</div><!-- End .dropdown -->


@push('scripts')
<script type="text/javascript">

	function remove(id)
	{

		let array = localStorage.getItem('products_cart');
		array = JSON.parse(array);
		let indexFound = array.findIndex( x=> x.id == id)
		array.splice(indexFound, 1);
		localStorage.setItem('products_cart', JSON.stringify( array ) );
		populate();
		calculatetotal();

	}

	function calculatetotal()
	{
		let array = localStorage.getItem('products_cart');
		array = JSON.parse(array);
		let total = 0;
		array.forEach(element => {
			let qty = parseInt(element.quantity) || 1;
			let price = parseFloat(element.sale_unit_price) || 0;
			total += price * qty;
		});

		$(".cart-total-price").empty();
		$(".cart-total-price").append(total.toFixed(2));
	}

	function populate()
	{
		$(".dropdown-cart-products").empty();
		$(".cart-count").empty();
		let count = 0;
		let totalItems = 0;
		let array = localStorage.getItem('products_cart');
		array = JSON.parse(array)
		count = array.length;

		const defaultImagePath = '{{ $defaultImagePath }}';

		array.forEach(element => {
			let qty = parseInt(element.quantity) || 1;
			totalItems += qty;
			const imagePath = (element.image_small && element.image_small !== 'imagen-no-disponible.jpg')
				? `/storage/uploads/items/${element.image_small}`
				: defaultImagePath;
			$(".dropdown-cart-products").append( `
					<div class="product cart-product-row">
						<div class="product-details">
						<h4 class="product-title">
							<a href="$">${element.description}</a>
						</h4>
						<span class="cart-product-info">
							<span class="cart-product-qty">${qty}</span> x ${element.sale_unit_price}
						</span>
						</div>
						<figure class="product-image-container">
							<a href="#" class="product-image">
								<img alt="${element.description}" src="${imagePath}" />
							</a>
							<a href="#" onclick="remove(${element.id})" class="btn-remove" title="Remove Product">
								<i class="icon-cancel"></i>
							</a>
						</figure>
					</div>`
				);
		});

		if(count > 0){
		    $(".cart-count").append(count).show();
		}else{
		    $(".cart-count").hide();
		}
	}


	$(function(){
		'use strict';
		populate();
		calculatetotal();

		window.addEventListener('productAddedToCart', function() {
			populate();
			calculatetotal();
		});
	});
</script>
@endpush
