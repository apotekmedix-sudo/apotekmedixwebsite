<style>
    .products-main .medicines-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.25rem;
        align-items: stretch;
    }
    .products-main .medicine-card {
        padding: 0.75rem;
        border: 1px solid #dbe3ec;
        border-radius: 18px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08);
        gap: 0;
    }
    .products-main .medicine-card:hover {
        transform: translateY(-3px);
        border-color: #bfdbfe;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.13);
    }
    .medicine-card-brand {
        padding: 0.05rem 0.15rem 0.55rem;
        color: #111827;
        font-size: 0.82rem;
        font-weight: 600;
        text-align: right;
    }
    .products-main .medicine-image {
        width: 100%;
        height: auto;
        aspect-ratio: 1 / 0.92;
        border-radius: 12px;
        background: #e4f3ff;
        font-size: 2rem;
    }
    .product-logo-marker {
        position: absolute;
        z-index: 2;
        top: 0.6rem;
        right: 0.6rem;
        width: 1.35rem;
        height: 1.35rem;
        border: 2px solid #0f172a;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.25);
    }
    .product-logo-marker--green { background: #22a447; }
    .product-logo-marker--red { background: #dc2626; }
    .product-logo-marker--blue { background: #2563eb; }
    .products-main .medicine-image img {
        object-fit: contain;
        padding: 0.55rem;
    }
    .products-main .medicine-body {
        padding: 0.8rem 0.15rem 0.15rem;
        gap: 0.45rem;
    }
    .products-main .medicine-company {
        display: none;
    }
    .medicine-card-principal {
        overflow: hidden;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .products-main .medicine-name {
        flex: initial;
        margin: 0;
        color: #111827;
        font-size: 1.15rem;
        line-height: 1.25;
    }
    .products-main .medicine-name a {
        color: inherit;
        text-decoration: none;
    }
    .products-main .medicine-name a:hover {
        color: #1d4ed8;
    }
    .products-main .medicine-name a:focus-visible {
        border-radius: 2px;
        outline: 2px solid #2563eb;
        outline-offset: 3px;
    }
    .medicine-card-details {
        display: grid;
        gap: 0.2rem;
        color: #1f2937;
        font-size: 0.82rem;
        line-height: 1.35;
    }
    .medicine-card-details p {
        display: -webkit-box;
        margin: 0;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }
    .medicine-card-details strong {
        font-weight: 700;
    }
    .products-main .medicine-price {
        display: none;
    }
    .product-unit-data {
        display: none;
    }
    .product-unit-section {
        margin-top: 0.15rem;
        text-align: center;
    }
    .product-unit-heading {
        display: block;
        margin-bottom: 0.35rem;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 600;
    }
    .product-unit-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(72px, 1fr));
        gap: 0.4rem;
    }
    .product-unit-grid-1 {
        grid-template-columns: minmax(92px, 120px);
        justify-content: center;
    }
    .product-unit-card {
        display: flex;
        min-width: 0;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
        gap: 0.2rem;
        padding: 0.4rem 0.3rem;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        color: #111827;
        background: #fff;
    }
    .product-unit-card.is-selected {
        border-color: #0f172a;
        box-shadow: 0 0 0 1px #0f172a;
        background: #f8fafc;
    }
    .product-unit-choice {
        display: flex;
        width: 100%;
        min-height: 2.9rem;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.2rem;
        padding: 0;
        border: 0;
        color: inherit;
        background: transparent;
        cursor: pointer;
    }
    .product-unit-choice:focus-visible {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
        border-radius: 5px;
    }
    .product-unit-name {
        overflow: hidden;
        max-width: 100%;
        font-size: 0.78rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .product-unit-price {
        font-size: 0.79rem;
        line-height: 1.2;
        white-space: nowrap;
    }
    .product-unit-old-price {
        color: #94a3b8;
        font-size: 0.66rem;
        text-decoration: line-through;
    }
    .product-unit-quantity {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        gap: 0.15rem;
        padding: 0.12rem 0.2rem;
        border: 1px solid #e5e7eb;
        border-radius: 7px;
    }
    .product-unit-quantity button {
        padding: 0;
        border: 0;
        color: #111827;
        background: transparent;
        font-size: 0.9rem;
        line-height: 1.2;
    }
    .product-unit-quantity input {
        width: 2rem;
        padding: 0;
        border: 0;
        color: #111827;
        background: transparent;
        font-size: 0.75rem;
        text-align: center;
        appearance: textfield;
        -moz-appearance: textfield;
    }
    .product-unit-quantity input::-webkit-inner-spin-button,
    .product-unit-quantity input::-webkit-outer-spin-button {
        margin: 0;
        -webkit-appearance: none;
    }
    .product-unit-quantity button:disabled,
    .product-unit-quantity input:disabled {
        color: #cbd5e1;
        cursor: not-allowed;
    }
    .product-unit-unavailable {
        display: block;
        color: #b45309;
        font-size: 0.78rem;
    }
    .products-main .product-stock,
    .products-main .stock-badge {
        align-self: flex-start;
        margin: 0;
        font-size: 0.72rem;
    }
    .products-main .medicine-meta {
        margin: 0 !important;
        color: #475569;
        font-size: 0.75rem;
    }
    .products-main .medicine-btn {
        margin-top: 0.1rem;
        padding: 0.2rem 0;
        color: #1d4ed8;
        background: transparent;
        font-size: 0.78rem;
        text-align: center;
    }
    .products-main .medicine-btn:hover {
        color: #1e40af;
        background: transparent;
        box-shadow: none;
        transform: none;
    }
    .products-main .btn-cart {
        margin-top: auto;
        padding: 0.7rem 0.55rem;
        border: 0;
        border-radius: 10px;
        color: #fff;
        background: #0f172a;
        box-shadow: 0 5px 12px rgba(15, 23, 42, 0.18);
        font-size: 0.83rem;
    }
    .products-main .btn-cart:hover {
        color: #fff;
        background: #1e293b;
    }
    .products-main .btn-cart.added {
        color: #065f46;
        background: #d1fae5;
    }
    @media (max-width: 1100px) {
        .products-main .medicines-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 760px) {
        .products-main .medicines-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .products-main .medicine-card { padding: 0.6rem; border-radius: 14px; }
        .products-main .medicine-name { font-size: 0.95rem; }
        .medicine-card-details { font-size: 0.75rem; }
        .product-unit-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .product-unit-name { font-size: 0.7rem; }
        .product-unit-price { font-size: 0.7rem; }
        .products-main .medicine-btn,
        .products-main .btn-cart { font-size: 0.74rem; }
    }
    @media (max-width: 380px) {
        .products-main .medicines-grid { grid-template-columns: minmax(0, 1fr); }
        .product-unit-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
</style>
