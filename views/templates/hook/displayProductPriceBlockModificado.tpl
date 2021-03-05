{if $productShowPrice}
  <div class="product-prices">
      {if $productHasDiscount}
        <div class="product-discount">
          <p>Mira un precio</p>
          <span class="regular-price">{$productRegularPrice}</span>
        </div>
      {/if}
      <div
        class="product-price h5 {if $productHasDiscount}has-discount{/if}"
        itemprop="offers"
        itemscope
        itemtype="https://schema.org/Offer"
      >

        <div class="current-price">
          <span itemprop="price">{$productPrice}</span>

          {if $productHasDiscount}
            {if $productDiscountType === 'percentage'}
              <span class="discount discount-percentage">{l s='Save %percentage%' d='Shop.Theme.Catalog' sprintf=['%percentage%' => $productDiscountPercentageAbsolute]}</span>
            {/if}
          {/if}
        </div>
      </div>
  </div>
  
{/if}