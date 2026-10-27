@include('customer-sales.create')

<script>
document.addEventListener('DOMContentLoaded', () => {
    const finalPrice = document.querySelector('input[name="final_price"]');
    const unitPrice = document.querySelector('input[name="unit_price"]');
    const quantity = document.querySelector('input[name="quantity"]');
    if (!finalPrice || !unitPrice || !quantity || document.querySelector('input[name="discount_amount"]')) return;

    const label = document.createElement('label');
    label.className = 'text-sm font-semibold';
    label.textContent = 'Discount amount';
    const discount = document.createElement('input');
    discount.name = 'discount_amount';
    discount.type = 'number';
    discount.min = '0';
    discount.step = '.01';
    discount.value = '0';
    discount.className = 'mt-2 block w-full rounded-md border-slate-300 py-2 text-sm';
    label.append(discount);
    finalPrice.parentElement.before(label);
    finalPrice.readOnly = true;
    finalPrice.classList.add('border-slate-200', 'bg-slate-100');

    const calculate = () => {
        const gross = (Number(unitPrice.value) || 0) * (Number(quantity.value) || 0);
        const value = Math.max(0, Number(discount.value) || 0);
        discount.max = String(gross);
        finalPrice.value = Math.max(0, gross - value).toFixed(2);
    };
    [unitPrice, quantity, discount].forEach((input) => input.addEventListener('input', calculate));
    setTimeout(calculate, 0);
});
</script>
