@extends('layouts.frontend')

@section('title', 'Checkout & Pembayaran - Apotek Medix')

@php
  $consultationPrescriptionCheckout = $consultationPrescription !== null;
  $fullName = trim((string) ($consultationPrescription?->patient_name ?? ($defaultShippingAddress?->name ?? (auth()->check() && auth()->user()->isUser() ? auth()->user()->name : ''))));
  $checkoutPhone = $consultationPrescription?->patient_phone ?? $defaultShippingAddress?->phone;
  $nameParts = $fullName !== '' ? preg_split('/\s+/', $fullName, 2) : [];
  $defaultFirstName = count($nameParts) > 1 ? $nameParts[0] : '';
  $defaultLastName = count($nameParts) > 1 ? $nameParts[1] : ($nameParts[0] ?? '');
  $defaultContact = $defaultShippingAddress?->email ?? (auth()->check() ? auth()->user()->email : ($defaultShippingAddress?->phone ?? ''));
  $consultationPrescriptionPayload = $consultationPrescription ? [
    'id' => $consultationPrescription->id,
    'number' => $consultationPrescription->prescription_number,
    'items' => $consultationPrescription->prescribed_items ?? [],
  ] : null;
@endphp

@section('styles')
<style>
  /* ===== BANNER HEADER APOTEK MEDIX ===== */
  .products-header.checkout-banner {
    background: linear-gradient(125deg, #0643a8 0%, #0757c9 58%, #0875d9 100%) !important;
    padding: 2.15rem 0 1.9rem !important;
    position: relative;
    overflow: hidden;
    border-bottom: 2px solid rgba(6, 182, 212, 0.35);
  }
  .products-header.checkout-banner::before {
    content: '';
    position: absolute;
    top: -80px;
    right: -80px;
    width: 320px;
    height: 320px;
    background: radial-gradient(circle, rgba(6, 182, 212, 0.18) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
  }
  .co-banner-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
    position: relative;
    z-index: 1;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
  }
  .breadcrumb-custom {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    margin-bottom: 0.6rem;
    font-size: 0.86rem;
    flex-wrap: wrap;
  }
  .breadcrumb-custom a {
    color: rgba(255, 255, 255, 0.75);
    text-decoration: none;
    transition: color 0.2s;
  }
  .breadcrumb-custom a:hover {
    color: #ffffff;
  }
  .breadcrumb-custom span {
    color: rgba(255, 255, 255, 0.45);
  }
  .breadcrumb-custom .current {
    color: #a5d65a;
    font-weight: 600;
  }
  .co-banner-title {
    font-size: clamp(1.65rem, 2.8vw, 2.25rem);
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 0.3rem;
    letter-spacing: -0.01em;
  }
  .co-banner-sub {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.92rem;
    margin: 0;
  }
  /* ===== MAIN 2-COLUMN CHECKOUT CONTAINER ===== */
  .co-main-wrap {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1.5rem 5rem;
    position: relative;
    z-index: 1;
  }
  .co-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
    gap: 2rem;
    align-items: start;
  }

  /* ===== LEFT CARD (FORM) ===== */
  .co-left-col {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    padding: 2.25rem 2.35rem;
    position: relative;
    overflow: hidden;
  }
  .co-left-col::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #0F172A 0%, #06B6D4 60%, #10B981 100%);
  }
  .co-left-inner {
    width: 100%;
  }

  /* ===== RIGHT CARD (ORDER SUMMARY) ===== */
  .co-right-col {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    padding: 2rem 1.85rem;
    position: sticky;
    top: 90px;
    overflow: hidden;
  }
  .co-right-col::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #06B6D4 0%, #0E7490 50%, #0F172A 100%);
  }
  .co-right-inner {
    width: 100%;
  }
  .co-summary-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 1rem;
    margin-bottom: 1.25rem;
    border-bottom: 1.5px solid #f1f5f9;
  }
  .co-summary-head h3 {
    margin: 0;
    font-size: 1.08rem;
    font-weight: 700;
    color: #0F172A;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .co-summary-head h3 i {
    color: #0E7490;
  }

  /* ===== SECTIONS & HEADINGS ===== */
  .co-section {
    margin-bottom: 2rem;
    padding-bottom: 1.75rem;
    border-bottom: 1px solid #f1f5f9;
  }
  .co-section:last-of-type {
    border-bottom: none;
    padding-bottom: 0;
  }
  .co-sec-head {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 0.9rem;
  }
  .co-sec-title {
    font-size: 1.18rem;
    font-weight: 700;
    color: #0F172A;
    margin: 0;
  }
  .co-sec-sub {
    font-size: 0.83rem;
    color: #64748b;
    margin: -0.45rem 0 0.9rem;
  }
  .co-sub-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0F172A;
    margin: 1.35rem 0 0.65rem;
  }
  .co-login-link {
    font-size: 0.85rem;
    font-weight: 600;
    color: #0E7490;
    text-decoration: underline;
    text-underline-offset: 3px;
  }
  .co-login-link:hover {
    color: #0F172A;
  }

  /* ===== INPUTS & FLOATING FIELDS ===== */
  .co-field {
    position: relative;
    margin-bottom: 0.75rem;
  }
  .co-input, .co-select {
    width: 100%;
    height: 48px;
    padding: 0.75rem 0.95rem;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    font-size: 0.9rem;
    color: #0F172A;
    background: #f8fafc;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
  }
  .co-input:focus, .co-select:focus {
    border-color: #06B6D4;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.14);
  }
  .co-field.has-label .co-select,
  .co-field.has-label .co-input {
    padding-top: 1.35rem;
    padding-bottom: 0.35rem;
  }
  .co-float-lbl {
    position: absolute;
    top: 6px;
    left: 0.95rem;
    font-size: 0.69rem;
    font-weight: 600;
    color: #64748b;
    pointer-events: none;
  }
  .co-field-icon {
    position: absolute;
    right: 0.9rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.9rem;
    pointer-events: none;
  }
  .co-help-wrap {
    position: absolute;
    right: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 0.95rem;
    cursor: pointer;
    z-index: 5;
  }
  .co-help-wrap:hover,
  .co-help-wrap:focus {
    color: #0F172A;
    outline: none;
  }
  .co-help-tooltip {
    position: absolute;
    bottom: calc(100% + 10px);
    right: -12px;
    width: 215px;
    background: #18181b;
    color: #ffffff;
    font-size: 0.76rem;
    font-weight: 500;
    line-height: 1.4;
    text-align: center;
    padding: 0.6rem 0.75rem;
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.22);
    opacity: 0;
    visibility: hidden;
    transform: translateY(4px);
    transition: all 0.16s ease;
    pointer-events: none;
  }
  .co-help-tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    right: 16px;
    border-width: 6px;
    border-style: solid;
    border-color: #18181b transparent transparent transparent;
  }
  .co-help-wrap:hover .co-help-tooltip,
  .co-help-wrap:focus .co-help-tooltip,
  .co-field:focus-within .co-help-tooltip {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
  }
  .co-billing-panel {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 1rem;
  }
  .co-billing-panel .co-input {
    background: #ffffff;
  }
  .co-row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
  }

  /* ===== CHECKBOX ===== */
  .co-check-label {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.85rem;
    color: #334155;
    margin-top: 0.15rem;
    user-select: none;
  }
  .co-check-label input[type="checkbox"] {
    width: 17px;
    height: 17px;
    accent-color: #0E7490;
    border-radius: 4px;
    cursor: pointer;
  }

  /* ===== DELIVERY SEGMENTED TOGGLE ===== */
  .co-delivery-tabs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    background: #f1f5f9;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    padding: 4px;
    gap: 4px;
    margin-bottom: 1rem;
  }
  .co-delivery-tab {
    height: 44px;
    border: 1.5px solid transparent;
    border-radius: 9px;
    background: transparent;
    color: #475569;
    font-size: 0.88rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.18s;
  }
  .co-delivery-tab.active {
    background: #ffffff;
    border-color: #06B6D4;
    color: #0F172A;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
  }
  .co-delivery-tab.active i {
    color: #0E7490;
  }

  /* ===== SHIPPING METHOD BOX ===== */
  .co-ship-box {
    background: #f8fafc;
    border: 1.5px dashed #cbd5e1;
    border-radius: 10px;
    padding: 1rem 1.05rem;
    font-size: 0.85rem;
    color: #64748b;
    line-height: 1.45;
  }
  .co-ship-box.ready {
    background: #ECFEFF;
    border: 1.5px solid #06B6D4;
    color: #0F172A;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
  }

  /* ===== PAYMENT OPTIONS IN RIGHT COLUMN ===== */
  .co-right-payment-box {
    margin-top: 1.5rem;
    padding-top: 1.4rem;
    border-top: 1.5px solid #e2e8f0;
  }
  .co-right-pay-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.35rem;
  }
  .co-right-pay-head h3 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: #0F172A;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .co-right-pay-head h3 i {
    color: #0E7490;
  }
  .co-right-pay-sub {
    font-size: 0.8rem;
    color: #64748b;
    margin: 0 0 0.85rem;
  }
  .co-pay-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.65rem;
    margin-bottom: 0.85rem;
  }
  .co-pay-opt {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    min-height: 50px;
    padding: 0.7rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #ffffff;
    color: #243b53;
    font-size: 0.88rem;
    font-weight: 700;
    text-align: left;
    transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease, box-shadow 0.18s ease;
  }
  .co-pay-opt i {
    font-size: 1.05rem;
    color: #0755c8;
    width: 20px;
    text-align: center;
    transition: color 0.18s ease;
  }
  .co-pay-opt:hover {
    transform: translateY(-1px);
    border-color: #0755c8;
    background: #ffffff;
  }
  .co-pay-opt.active {
    background: #eff6ff;
    border-color: #0755c8;
    color: #10243e;
    font-weight: 700;
    box-shadow: 0 3px 10px rgba(7, 85, 200, 0.14);
  }
  .co-pay-opt.active i {
    color: #0755c8;
  }
  .co-pay-opt:focus-visible {
    outline: 3px solid rgba(7, 85, 200, 0.25);
    outline-offset: 2px;
  }
  .co-pay-opt:disabled {
    border-color: #e2e8f0;
    background: #f1f5f9;
    color: #94a3b8;
    cursor: not-allowed;
    box-shadow: none;
  }
  .co-pay-opt:disabled i {
    color: #94a3b8;
  }
  .co-tempo-disabled-label {
    display: block;
    font-size: 0.68rem;
    font-weight: 500;
  }
  .co-tempo-disabled-label[hidden] {
    display: none;
  }
  .co-payment-detail {
    margin: 0 0 0.85rem;
    padding: 0.9rem 1rem;
    border: 1px solid #dbe4f0;
    border-radius: 10px;
    background: #f8fafc;
    color: #334155;
    text-align: center;
  }
  .co-payment-detail[hidden] {
    display: none;
  }
  .co-qris-image {
    width: 150px;
    height: 150px;
    margin: 0 auto 0.6rem;
    padding: 0.55rem;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
  }
  .co-payment-detail-title {
    display: block;
    margin-bottom: 0.25rem;
    color: #0f172a;
    font-size: 0.88rem;
    font-weight: 800;
  }
  .co-payment-detail-note {
    margin: 0;
    color: #64748b;
    font-size: 0.77rem;
    line-height: 1.45;
  }
  #coPaymentProof {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    clip-path: inset(50%);
    border: 0;
  }
  .co-proof-upload {
    position: relative;
    display: grid;
    justify-items: center;
    gap: 0.55rem;
    margin: 0.65rem 0 0;
    padding: 1rem;
    border: 1.5px dashed #9bb8de;
    border-radius: 10px;
    background: #ffffff;
    color: #334155;
    cursor: pointer;
    transition: background 0.18s ease, border-color 0.18s ease;
  }
  .co-proof-upload:hover,
  .co-proof-upload:focus-within {
    border-color: #0755c8;
    background: #f8fbff;
  }
  .co-proof-upload-icon {
    display: grid;
    width: 42px;
    height: 42px;
    place-items: center;
    border-radius: 50%;
    background: #eff6ff;
    color: #0755c8;
    font-size: 1.05rem;
  }
  .co-proof-upload-copy {
    display: grid;
    gap: 0.15rem;
    text-align: center;
  }
  .co-proof-upload-copy strong {
    color: #10243e;
    font-size: 0.84rem;
  }
  .co-proof-upload-copy span {
    color: #64748b;
    font-size: 0.75rem;
    line-height: 1.4;
  }
  .co-proof-upload-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 34px;
    padding: 0.4rem 0.75rem;
    border: 1px solid #0755c8;
    border-radius: 7px;
    background: #fff;
    color: #0755c8;
    font-size: 0.78rem;
    font-weight: 700;
  }
  .co-proof-preview {
    display: none;
    max-width: 100%;
    max-height: 160px;
    border: 1px solid #dbe4f0;
    border-radius: 8px;
    background: #fff;
    object-fit: contain;
  }
  .co-proof-file-name {
    max-width: 100%;
    overflow-wrap: anywhere;
    color: #166534;
    font-size: 0.77rem;
    font-weight: 700;
  }
  .co-proof-remove {
    display: none;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    min-height: 32px;
    padding: 0.35rem 0.65rem;
    border: 1px solid #fecaca;
    border-radius: 7px;
    background: #fff;
    color: #b91c1c;
    font: inherit;
    font-size: 0.76rem;
    font-weight: 700;
    cursor: pointer;
  }
  .co-proof-remove:hover {
    background: #fef2f2;
  }
  .co-proof-upload.has-file {
    border-style: solid;
    border-color: #86c99b;
    background: #f7fef9;
  }
  .co-pre-submit-notice {
    position: fixed !important;
    inset: 0 !important;
    margin: 0 !important;
    display: none;
    width: 100vw !important;
    height: 100vh !important;
    max-width: none;
    max-height: none;
    padding: 1rem;
    overflow: auto;
    border: 0 !important;
    border-radius: 0;
    transform: none !important;
    background: transparent;
    color: #172b4d;
    box-shadow: none;
  }
  .co-pre-submit-notice[open] {
    display: flex !important;
    align-items: center;
    justify-content: center;
  }
  .co-pre-submit-notice::backdrop {
    background: rgba(15, 23, 42, 0.6);
  }
  .co-pre-submit-notice-card {
    position: relative;
    width: min(440px, 100%);
    max-height: calc(100vh - 2rem);
    overflow: auto;
    padding: 1.6rem;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.3);
    text-align: center;
  }
  .co-pre-submit-notice-close {
    position: absolute;
    top: 0.7rem;
    right: 0.7rem;
    display: grid;
    width: 34px;
    height: 34px;
    place-items: center;
    border: 0;
    border-radius: 50%;
    background: #f1f5f9;
    color: #475569;
    cursor: pointer;
    font-size: 1rem;
  }
  .co-pre-submit-notice-close:hover {
    background: #e2e8f0;
  }
  .co-pre-submit-notice-icon {
    display: grid;
    width: 54px;
    height: 54px;
    margin: 0 auto 0.85rem;
    place-items: center;
    border-radius: 50%;
    background: #eff6ff;
    color: #0755c8;
    font-size: 1.35rem;
  }
  .co-pre-submit-notice-icon.is-warning {
    background: #fff7ed;
    color: #c2410c;
  }
  .co-pre-submit-notice-card h2 {
    margin: 0 2rem 0.45rem;
    color: #10243e;
    font-size: 1.12rem;
    font-weight: 800;
  }
  .co-pre-submit-notice-card p {
    margin: 0 0 1rem;
    color: #52657e;
    font-size: 0.88rem;
    line-height: 1.55;
  }
  #coPreSubmitNoticeProducts {
    display: grid;
    gap: 0.4rem;
    max-height: 150px;
    overflow: auto;
    margin: 0 0 1rem;
    padding: 0;
    list-style: none;
    text-align: left;
  }
  #coPreSubmitNoticeProducts:not([hidden]) {
    display: grid;
  }
  #coPreSubmitNoticeProducts[hidden] {
    display: none !important;
  }
  #coPreSubmitNoticeProducts li {
    padding: 0.55rem 0.7rem;
    border: 1px solid #fed7aa;
    border-radius: 8px;
    background: #fff7ed;
    color: #7c2d12;
    font-size: 0.82rem;
    font-weight: 700;
  }
  .co-pre-submit-notice-continue {
    display: inline-flex;
    min-width: 150px;
    min-height: 44px;
    align-items: center;
    justify-content: center;
    padding: 0.65rem 1rem;
    border: 0;
    border-radius: 9px;
    background: #0755c8;
    color: #fff;
    font: inherit;
    font-size: 0.88rem;
    font-weight: 800;
    cursor: pointer;
  }
  .co-pre-submit-notice-continue:hover {
    background: #063583;
  }
  @media (prefers-reduced-motion: reduce) {
    .co-pay-opt,
    .co-proof-upload {
      transition: none;
    }
  }
  .co-transfer-number {
    display: block;
    margin: 0.3rem 0;
    color: #0f172a;
    font-size: 1.25rem;
    font-weight: 800;
    letter-spacing: 0.04em;
  }
  .co-cash-amount {
    display: block;
    color: #0e7490;
    font-size: 1.3rem;
    font-weight: 800;
  }

  /* ===== BILLING ADDRESS RADIO GROUP ===== */
  .co-radio-group {
    border: 1.5px solid #cbd5e1;
    border-radius: 12px;
    overflow: visible;
    background: #ffffff;
  }
  .co-radio-row:first-child {
    border-radius: 10px 10px 0 0;
  }
  .co-radio-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.92rem 1.05rem;
    font-size: 0.88rem;
    font-weight: 500;
    color: #0F172A;
    border-bottom: 1px solid #e2e8f0;
    transition: background 0.15s;
  }
  .co-radio-row:last-child {
    border-bottom: none;
  }
  .co-radio-row.active {
    background: #ECFEFF;
    font-weight: 600;
  }
  .co-radio-row input[type="radio"] {
    width: 17px;
    height: 17px;
    accent-color: #0E7490;
    margin: 0;
  }

  /* ===== SUBMIT BUTTON & FOOTER ===== */
  .co-submit-btn {
    width: 100%;
    height: 52px;
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
    color: #ffffff;
    border: 1.5px solid #06B6D4;
    border-radius: 12px;
    font-size: 0.98rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    margin-top: 0.75rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.55rem;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.16);
    transition: all 0.2s;
  }
  .co-submit-btn:hover {
    background: linear-gradient(135deg, #1E293B 0%, #0E7490 100%);
    transform: translateY(-2px);
    box-shadow: 0 12px 26px rgba(6, 182, 212, 0.24);
  }
  .co-submit-btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
    transform: none;
  }
  .co-error-box {
    display: none;
    background: #fef2f2;
    border: 1.5px solid #fecaca;
    color: #991b1b;
    padding: 0.9rem 1rem;
    border-radius: 10px;
    font-size: 0.86rem;
    font-weight: 500;
    margin-top: 1rem;
  }
  .co-footer-links {
    margin-top: 1.75rem;
    padding-top: 1rem;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  .co-footer-links a {
    font-size: 0.8rem;
    font-weight: 600;
    color: #0E7490;
    text-decoration: underline;
    text-underline-offset: 3px;
  }

  /* ===== RIGHT COLUMN SUMMARY ITEMS ===== */
  .co-sum-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.15rem;
    padding-bottom: 1.15rem;
    border-bottom: 1px solid #f1f5f9;
  }
  .co-sum-item:last-child {
    border-bottom: none;
    padding-bottom: 0.25rem;
  }
  .co-sum-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    min-width: 0;
  }
  .co-sum-thumb {
    width: 68px;
    height: 68px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
    border: 1.5px solid #e2e8f0;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.35rem;
    flex-shrink: 0;
    box-shadow: 0 3px 10px rgba(15, 23, 42, 0.05);
  }
  .co-sum-logo-marker {
    position: absolute;
    z-index: 2;
    top: -8px;
    right: -8px;
    display: inline-flex;
    width: 20px;
    height: 20px;
    align-items: center;
    justify-content: center;
    border: 1.5px solid #fff;
    border-radius: 50%;
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.3);
  }
  .co-sum-logo-marker--green { background: #22a447; }
  .co-sum-logo-marker--blue { background: #2563eb; }
  .co-sum-logo-marker--red {
    background: #dc2626;
    color: #0f172a;
    font-size: 0.68rem;
    font-weight: 900;
    line-height: 1;
  }
  .co-sum-thumb img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
  }
  .co-sum-name {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0F172A;
    line-height: 1.35;
  }
  .co-sum-meta {
    font-size: 0.76rem;
    font-weight: 600;
    color: #0E7490;
    margin-top: 3px;
  }
  .co-sum-detail {
    margin-top: 4px;
    color: #64748b;
    font-size: 0.76rem;
    line-height: 1.4;
  }
  .co-sum-actions {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    margin-top: 0.55rem;
  }
  .co-sum-quantity {
    display: inline-flex;
    align-items: center;
    height: 30px;
    overflow: hidden;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
  }
  .co-sum-quantity button {
    width: 30px;
    height: 28px;
    border: 0;
    background: #f8fafc;
    color: #0f172a;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 700;
  }
  .co-sum-quantity button:disabled {
    color: #cbd5e1;
    cursor: not-allowed;
  }
  .co-sum-quantity input {
    width: 38px;
    height: 28px;
    padding: 0;
    border: 0;
    outline: none;
    color: #0f172a;
    font: inherit;
    font-size: 0.78rem;
    font-weight: 700;
    text-align: center;
    -moz-appearance: textfield;
  }
  .co-sum-quantity input::-webkit-inner-spin-button,
  .co-sum-quantity input::-webkit-outer-spin-button {
    margin: 0;
    -webkit-appearance: none;
  }
  .co-sum-remove {
    display: inline-flex;
    width: 30px;
    height: 30px;
    align-items: center;
    justify-content: center;
    border: 1px solid #fecaca;
    border-radius: 8px;
    background: #fff;
    color: #dc2626;
    cursor: pointer;
  }
  .co-sum-remove:hover {
    background: #fef2f2;
  }
  .co-sum-price {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0F172A;
    white-space: nowrap;
  }

  .co-disc-row {
    display: flex;
    gap: 0.65rem;
    margin: 1.25rem 0 1.35rem;
    padding-top: 0.75rem;
    border-top: 1.5px solid #f1f5f9;
  }
  .co-disc-inp {
    flex: 1;
    height: 46px;
    padding: 0 0.9rem;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    background: #f8fafc;
    font-size: 0.88rem;
    color: #0F172A;
    outline: none;
    transition: border-color 0.15s, background 0.15s;
  }
  .co-disc-inp:focus {
    border-color: #06B6D4;
    background: #ffffff;
  }
  .co-disc-btn {
    height: 46px;
    padding: 0 1.2rem;
    border: 1.5px solid #06B6D4;
    border-radius: 10px;
    background: #ECFEFF;
    color: #0E7490;
    font-size: 0.86rem;
    font-weight: 700;
    transition: all 0.15s;
  }
  .co-disc-btn:hover {
    background: #0F172A;
    color: #ffffff;
  }

  .co-totals-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.88rem;
    color: #475569;
    margin-bottom: 0.65rem;
  }
  .co-totals-grand {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-top: 1rem;
    padding-top: 1.1rem;
    border-top: 1.5px solid #e2e8f0;
  }
  .co-totals-grand-lbl {
    font-size: 1.12rem;
    font-weight: 800;
    color: #0F172A;
  }
  .co-totals-grand-val {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0E7490;
  }
  .co-totals-grand-val small {
    font-size: 0.74rem;
    font-weight: 600;
    color: #64748b;
    margin-right: 0.35rem;
  }

  /* ===== SUCCESS PANEL ===== */
  .co-success-card {
    display: none;
    background: #f8fafc;
    border: 1.5px solid #06B6D4;
    border-radius: 14px;
    padding: 1.75rem;
    margin-top: 0.5rem;
  }
  .co-action-btn {
    width: 100%;
    padding: 0.88rem 1rem;
    border-radius: 10px;
    font-size: 0.92rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-decoration: none;
    margin-bottom: 0.75rem;
    border: none;
    font: inherit;
    cursor: pointer;
    transition: transform 0.15s;
  }
  .co-action-btn:hover {
    transform: translateY(-1px);
  }
  .co-action-btn:disabled {
    opacity: 0.65;
    cursor: wait;
    transform: none;
  }
  .co-receipt-dialog {
    width: min(940px, calc(100% - 1.5rem));
    max-width: none;
    height: min(92vh, 900px);
    height: min(92dvh, 900px);
    max-height: none;
    padding: 0;
    border: 0;
    border-radius: 16px;
    overflow: hidden;
    background: #fff;
    color: #0f172a;
    box-shadow: 0 24px 70px rgba(15, 23, 42, .35);
  }
  .co-receipt-dialog::backdrop {
    background: rgba(15, 23, 42, .72);
  }
  .co-receipt-dialog-layout {
    display: grid;
    grid-template-rows: auto minmax(0, 1fr) auto;
    width: 100%;
    height: 100%;
  }
  .co-receipt-dialog-toolbar,
  .co-receipt-dialog-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .8rem 1rem;
    background: #fff;
  }
  .co-receipt-dialog-toolbar {
    border-bottom: 1px solid #e2e8f0;
  }
  .co-receipt-dialog-toolbar strong {
    font-size: .98rem;
  }
  .co-receipt-dialog-toolbar button,
  .co-receipt-dialog-actions button {
    min-height: 42px;
    padding: .6rem .9rem;
    border: 0;
    border-radius: 9px;
    font: inherit;
    font-weight: 700;
    cursor: pointer;
  }
  .co-receipt-dialog-close {
    background: #f1f5f9;
    color: #334155;
  }
  .co-receipt-frame {
    display: block;
    width: 100%;
    height: 100%;
    border: 0;
    background: #f1f5f9;
  }
  .co-receipt-dialog-actions {
    justify-content: flex-end;
    border-top: 1px solid #e2e8f0;
  }
  .co-receipt-print {
    background: #0755c8;
    color: #fff;
  }
  .co-receipt-whatsapp {
    background: #10b981;
    color: #fff;
  }
  @media (max-width: 520px) {
    .co-receipt-dialog {
      width: 100%;
      height: 100vh;
      height: 100dvh;
      border-radius: 0;
    }
    .co-receipt-dialog-toolbar,
    .co-receipt-dialog-actions {
      padding: .65rem;
    }
    .co-receipt-dialog-actions button {
      flex: 1;
      padding: .55rem .4rem;
      font-size: .82rem;
    }
  }

  @media (max-width: 960px) {
    .co-layout {
      grid-template-columns: 1fr;
    }
    .co-right-col {
      position: static;
    }
    .co-left-col {
      padding: 1.65rem 1.25rem;
    }
    .co-row-2 {
      grid-template-columns: 1fr;
    }
  }
</style>
@endsection

@section('content')
<section class="products-header checkout-banner">
  <i class="fa-solid fa-credit-card header-deco-icon header-deco-icon-1"></i>
  <i class="fa-solid fa-truck-fast header-deco-icon header-deco-icon-2"></i>
  <i class="fa-solid fa-shield-heart header-deco-icon header-deco-icon-3"></i>
  <div class="co-banner-inner">
    <div>
      <div class="breadcrumb-custom">
        <a href="{{ route('home') }}"><i class="fa-solid fa-house"></i> Beranda</a>
        <span>/</span>
        <a href="{{ route('cart.index') }}">Keranjang Anda</a>
        <span>/</span>
        <span class="current">Pembayaran &amp; Checkout</span>
      </div>
      <h1 class="co-banner-title">Checkout &amp; Pembayaran</h1>
      <p class="co-banner-sub">{{ $consultationPrescriptionCheckout ? 'Lengkapi alamat pengiriman untuk menerima obat yang diresepkan.' : 'Lengkapi alamat pengantaran dan metode pembayaran untuk menyelesaikan pesanan Anda.' }}</p>
    </div>
  </div>
</section>

<!-- MAIN CHECKOUT SPLIT VIEW -->
<div class="co-main-wrap">
  <div class="co-layout">
    <!-- LEFT COLUMN: FORMS -->
    <div class="co-left-col">
      <div class="co-left-inner">

        <div id="coFormWrapper">
          <!-- Fields khusus Admin PBF Purchase Order (hanya muncul jika mode Admin PBF) -->
          <div id="coAdminPbfFields" class="co-section" style="display:none;">
            <div class="co-sec-head">
              <h2 class="co-sec-title">Data PBF Tujuan</h2>
            </div>
            <div class="co-field">
              <select id="co_pbf_profile" class="co-input">
                <option value="medix">Identitas Apotek Medix</option>
                <option value="medikpedia">Identitas Apotek Medikpedia</option>
              </select>
            </div>
            <div class="co-field">
              <select id="co_pbf_order_type" class="co-input">
                <option value="regular">SP Biasa (Obat Reguler)</option>
                <option value="oot">SP OOT (Obat-Obat Tertentu)</option>
                <option value="precursor">SP Prekursor</option>
              </select>
            </div>
            <div class="co-field">
              <input type="text" id="co_pbf_name" class="co-input" placeholder="Nama PBF tujuan *">
            </div>
            <div class="co-field">
              <input type="text" id="co_pbf_address" class="co-input" placeholder="Alamat PBF tujuan *">
            </div>
            <div class="co-field">
              <input type="tel" id="co_pbf_phone" class="co-input" maxlength="50" placeholder="Nomor telepon PBF *">
            </div>
            <div class="co-row-2">
              <div class="co-field">
                <input type="text" id="co_signer_name" class="co-input" value="Apt. Hendri Setiyono, S Farm" placeholder="Nama Apoteker *">
              </div>
              <div class="co-field">
                <input type="text" id="co_signer_title" class="co-input" value="Apoteker Penanggung Jawab" placeholder="Jabatan *">
              </div>
            </div>
            <div class="co-row-2">
              <div class="co-field">
                <input type="text" id="co_signer_sia" class="co-input" value="13042600890450001" placeholder="Nomor SIA *">
              </div>
              <div class="co-field">
                <input type="text" id="co_signer_sipa" class="co-input" value="1/b.19/31.71.03.1007.4k-1.b/4/tm.09/e/2026" placeholder="Nomor SIPA *">
              </div>
            </div>
          </div>

          <!-- PENGANTARAN -->
          <div class="co-section">
            <div class="co-sec-head">
              <h2 class="co-sec-title">Pengantaran</h2>
            </div>

            @unless($consultationPrescriptionCheckout)
            <div class="co-delivery-tabs" id="coDeliveryTabs">
              <button type="button" class="co-delivery-tab active" id="tabShipBtn" onclick="setDeliveryMode('ship')">
                <i class="fa-solid fa-truck-fast"></i> Kirim
              </button>
              <button type="button" class="co-delivery-tab" id="tabPickupBtn" onclick="setDeliveryMode('pickup')">
                <i class="fa-solid fa-location-dot"></i> Ambil di tempat
              </button>
            </div>
            @endunless

            <div class="co-field">
              <input type="text" id="co_b2b_outlet" class="co-input" placeholder="Nama outlet *" style="display:none;margin-bottom:0.75rem;">
              <input type="text" id="co_full_name" class="co-input" placeholder="Nama" value="{{ $fullName }}" @if($consultationPrescriptionCheckout) readonly @endif>
              <input type="tel" id="co_phone" class="co-input" placeholder="No. Telp / WhatsApp" value="{{ $checkoutPhone }}" style="margin-top:0.75rem;" @if($consultationPrescriptionCheckout) readonly @endif>
            </div>

            <div id="coShippingAddressFields">
              <div class="co-field">
                <input type="text" id="co_address" class="co-input" placeholder="{{ $consultationPrescriptionCheckout ? 'Masukkan alamat pengiriman lengkap *' : 'Alamat lengkap' }}" value="{{ $consultationPrescriptionCheckout ? '' : $defaultShippingAddress?->address }}" oninput="updateShippingMethodStatus()">
              </div>
            </div>

            <div id="coPickupLocationBox" class="co-ship-box ready" style="display:none;margin-bottom:0.75rem;">
              <div>
                <div style="font-weight:700;color:#0F172A;">Apotek Medix Bogor</div>
                <div style="font-size:0.8rem;color:#475569;font-weight:500;">Jl. Raya Ciawi Prapatan No.256 C, Kota Bogor, Jawa Barat 16146</div>
              </div>
              <span style="font-weight:800;color:#0E7490;">Gratis</span>
            </div>

            <div class="co-field" style="display:none;margin-bottom:0;">
              <input type="tel" id="co_phone_unused" style="display:none;">
              <span class="co-help-wrap" tabindex="0" aria-label="Info nomor telepon">
                <i class="fa-regular fa-circle-question"></i>
                <span class="co-help-tooltip">Jika kami ingin menghubungi Anda terkait pesanan</span>
              </span>
            </div>

            <div id="coB2bPermitFields" style="display:none;margin-top:0.75rem;">
              <div class="co-row-2">
                <div class="co-field" style="margin-bottom:0;">
                  <input type="text" id="co_b2b_sia" class="co-input" placeholder="SIA *">
                </div>
                <div class="co-field" style="margin-bottom:0;">
                  <input type="text" id="co_b2b_sipa" class="co-input" placeholder="SIPA *">
                </div>
              </div>
            </div>

            <h3 class="co-sub-title">Alamat penagihan</h3>
            <div class="co-radio-group">
              <label class="co-radio-row active" id="radioBillSameRow">
                <input type="radio" name="billing_addr_mode" value="same" checked onchange="onBillingModeChange()">
                <span>Sama dengan alamat pengiriman</span>
              </label>
              <label class="co-radio-row" id="radioBillDiffRow">
                <input type="radio" name="billing_addr_mode" value="different" onchange="onBillingModeChange()">
                <span>Pakai alamat penagihan lain</span>
              </label>

              <div id="coBillingCustomFields" class="co-billing-panel" style="display:none;border-radius:0 0 10px 10px;">
                <div class="co-field">
                  <input type="text" id="co_billing_name" class="co-input" placeholder="Nama lengkap">
                </div>
                <div class="co-field">
                  <input type="text" id="co_billing_address" class="co-input" placeholder="Alamat penagihan lengkap">
                </div>
                <div class="co-field" style="margin-bottom:0;">
                  <input type="tel" id="co_billing_phone" class="co-input" placeholder="No. Telepon (opsional)" style="padding-right:2.5rem;">
                  <span class="co-help-wrap" tabindex="0" aria-label="Info nomor telepon">
                    <i class="fa-regular fa-circle-question"></i>
                    <span class="co-help-tooltip">Jika kami ingin menghubungi Anda terkait pesanan</span>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- PANEL KONFIRMASI SETELAH ORDER BERHASIL -->
        <div id="coSuccessPanel" class="co-success-card">
          <div style="display:flex;align-items:center;gap:0.85rem;margin-bottom:0.9rem;">
            <div style="width:46px;height:46px;border-radius:50%;background:#d1fae5;color:#065f46;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
              <i class="fa-solid fa-check"></i>
            </div>
            <div>
              <h2 style="margin:0;font-size:1.18rem;font-weight:800;color:#0F172A;">Pesanan Berhasil Dibuat!</h2>
              <p id="coOrderNumberLabel" style="margin:2px 0 0;font-size:0.85rem;color:#0E7490;font-weight:700;">ID Pesanan #--</p>
            </div>
          </div>
          <p id="coSuccessDesc" style="font-size:0.9rem;color:#334155;line-height:1.55;margin-bottom:1.25rem;">
            Pesanan Anda telah tercatat. Unduh struk dan konfirmasi pembayaran melalui WhatsApp agar admin dapat memverifikasi pesanan Anda.
          </p>

          <button type="button" id="coInvoiceBtn" onclick="openReceiptPreview()" class="co-action-btn" style="background:#0F172A;border:1.5px solid #06B6D4;color:#ffffff;">
            <i class="fa-solid fa-file-invoice"></i> Lihat Struk
          </button>
          <button type="button" id="coWhatsappBtn" onclick="openWhatsAppCheckout()" class="co-action-btn" style="background:#10B981;color:#ffffff;">
            <i class="fa-brands fa-whatsapp" style="font-size:1.15rem;"></i> Konfirmasi Pembayaran via WhatsApp
          </button>
          <a id="coBackToCatalogBtn" href="{{ route('home') }}" class="co-action-btn" style="background:#ffffff;color:#0F172A;border:1.5px solid #cbd5e1;margin-bottom:0;">
            Kembali ke Beranda
          </a>
        </div>

        <div class="co-footer-links">
          <a href="{{ route('about') }}">Ketentuan dan kebijakan Apotek Medix</a>
          <a id="coEditCartFooterLink" href="{{ route('cart.index') }}" @if($consultationPrescriptionCheckout) style="display:none" @endif>Ubah isi keranjang</a>
        </div>

      </div>
    </div>

    <!-- RIGHT COLUMN: ORDER SUMMARY & PAYMENT -->
    <div class="co-right-col">
      <div class="co-right-inner">
        <div class="co-summary-head">
          <h3><i class="fa-solid fa-receipt"></i> Ringkasan Pesanan</h3>
          <a id="coEditSummaryLink" href="{{ route('cart.index') }}" style="font-size:0.8rem;font-weight:600;color:#0E7490;text-decoration:underline;{{ $consultationPrescriptionCheckout ? 'display:none;' : '' }}">Ubah</a>
        </div>

        <div id="coSummaryItems"></div>
        <div id="consultationPrescriptionNotice" role="status" style="display:none;margin:.75rem 0;padding:.7rem .8rem;border:1px solid #bbf7d0;border-radius:9px;background:#f0fdf4;color:#166534;font-size:.84rem;"></div>

        <div id="coDiscountInputRow" class="co-disc-row" style="margin-bottom:0.5rem;">
          <input type="text" id="coDiscountCodeInput" class="co-disc-inp" placeholder="Masukkan kode diskon" style="text-transform:uppercase;">
          <button type="button" id="coDiscountApplyBtn" class="co-disc-btn" onclick="applyCheckoutDiscount()">Pakai</button>
        </div>
        <div id="coDiscountMsgBox" style="display:none;font-size:0.8rem;font-weight:600;margin-bottom:1rem;padding:0.55rem 0.75rem;border-radius:8px;"></div>

        <div class="co-totals-line">
          <span>Subtotal &middot; <span id="coTotalItemsCount">0</span> item</span>
          <span id="coSubtotalDisplay" style="font-weight:600;color:#0F172A;">Rp 0,00</span>
        </div>

        <div class="co-totals-line" id="coDiscountRow" style="display:none;color:#059669;font-weight:600;">
          <span>Diskon</span>
          <span id="coDiscountDisplay">-Rp 0,00</span>
        </div>

        <div class="co-totals-line">
          <span>Pengiriman <i class="fa-regular fa-circle-question" style="color:#94a3b8;font-size:0.78rem;"></i></span>
          <span id="coShippingSummaryText" style="color:#64748b;font-size:0.82rem;">Masukkan alamat pengiriman</span>
        </div>

        <div class="co-totals-grand">
          <span class="co-totals-grand-lbl">Total</span>
          <span class="co-totals-grand-val"><small>IDR</small><span id="coGrandTotalDisplay">Rp 0,00</span></span>
        </div>

        <!-- PEMBAYARAN DI SEBELAH KANAN -->
        <div id="coPaymentRightWrapper" class="co-right-payment-box">
          <div class="co-right-pay-head">
            <h3><i class="fa-solid fa-wallet"></i> Pembayaran</h3>
            <span style="font-size:0.75rem;color:#0E7490;font-weight:600;"><i class="fa-solid fa-lock"></i> Aman</span>
          </div>
          <p class="co-right-pay-sub">Pilih metode pembayaran pesanan Anda:</p>

          <input type="hidden" id="co_payment_method" value="{{ $defaultCheckoutPaymentMethod }}">

          <div class="co-pay-grid" id="coPayOptionsGrid">
            @if($paymentSettings?->hasQris())
              <button type="button" class="co-pay-opt {{ $defaultCheckoutPaymentMethod === 'QRIS' ? 'active' : '' }}" data-pay-method="QRIS" onclick="selectPaymentMethod('QRIS')">
                <i class="fa-solid fa-qrcode"></i>
                <span>QRIS</span>
              </button>
            @endif
            @if($paymentSettings?->hasBankTransfer())
              <button type="button" class="co-pay-opt {{ $defaultCheckoutPaymentMethod === 'Transfer' ? 'active' : '' }}" data-pay-method="Transfer" onclick="selectPaymentMethod('Transfer')">
                <i class="fa-solid fa-building-columns"></i>
                <span>Transfer</span>
              </button>
            @endif
            <button type="button" class="co-pay-opt {{ $defaultCheckoutPaymentMethod === 'Cash' ? 'active' : '' }}" data-pay-method="Cash" onclick="selectPaymentMethod('Cash')">
              <i class="fa-solid fa-money-bill-wave"></i>
              <span>Tunai</span>
            </button>
            <button type="button" class="co-pay-opt" id="coTempoOption" data-pay-method="Tempo" onclick="selectPaymentMethod('Tempo')" aria-disabled="false">
              <i class="fa-solid fa-calendar-day"></i>
              <span>Tempo<small id="coTempoDisabledLabel" class="co-tempo-disabled-label" hidden>B2B</small></span>
            </button>
          </div>

          @if($paymentSettings?->hasQris())
            <div id="coQrisDetails" class="co-payment-detail" aria-live="polite" @if($defaultCheckoutPaymentMethod !== 'QRIS') hidden @endif>
              <img class="co-qris-image" src="{{ $paymentSettings->qris_image_url }}" alt="QRIS pembayaran Apotek Medix">
              <span class="co-payment-detail-title">Scan QRIS Apotek Medix</span>
              @if($paymentSettings->payment_instructions)<p class="co-payment-detail-note">{{ $paymentSettings->payment_instructions }}</p>@endif
            </div>
          @else
            <div id="coQrisDetails" class="co-payment-detail" aria-live="polite" hidden></div>
          @endif
          <div id="coTransferDetails" class="co-payment-detail" aria-live="polite" hidden>
            @if($paymentSettings?->hasBankTransfer())
              <span class="co-payment-detail-title">Transfer {{ $paymentSettings->bank_name }}</span>
              <span class="co-transfer-number">{{ $paymentSettings->account_number }}</span>
              <p class="co-payment-detail-note">Atas nama {{ $paymentSettings->account_holder }}</p>
              @if($paymentSettings->payment_instructions)<p class="co-payment-detail-note">{{ $paymentSettings->payment_instructions }}</p>@endif
            @endif
          </div>
          <div id="coCashDetails" class="co-payment-detail" aria-live="polite" hidden>
            <span class="co-payment-detail-title">Nominal pembayaran</span>
            <span id="coCashAmount" class="co-cash-amount">Rp 0,00</span>
          </div>

          <div id="coPaymentProofWrap" class="co-payment-detail" hidden>
            <label for="coPaymentProof" class="co-payment-detail-title">Bukti pembayaran <span aria-hidden="true">*</span></label>
            <label id="coProofUpload" class="co-proof-upload" for="coPaymentProof">
              <span class="co-proof-upload-icon"><i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i></span>
              <span class="co-proof-upload-copy">
                <strong>Pilih bukti pembayaran</strong>
                <span>Foto atau PDF · Maksimal 5 MB</span>
              </span>
              <span class="co-proof-upload-button"><i class="fa-regular fa-folder-open" aria-hidden="true"></i> Pilih file</span>
              <img id="coProofPreview" class="co-proof-preview" alt="Pratinjau bukti pembayaran">
              <span id="coProofFileName" class="co-proof-file-name" aria-live="polite"></span>
              <input id="coPaymentProof" type="file" accept="image/jpeg,image/png,image/webp,application/pdf" aria-describedby="coPaymentProofNote">
            </label>
            <button type="button" id="coProofRemove" class="co-proof-remove">
              <i class="fa-solid fa-trash-can" aria-hidden="true"></i> Hapus file
            </button>
            <p id="coPaymentProofNote" class="co-payment-detail-note">Unggah bukti transfer atau pembayaran QRIS (JPG, PNG, WEBP, atau PDF; maks. 5 MB).</p>
          </div>

          <div id="coTempoDaysWrap" style="display:none;margin-bottom:0.75rem;">
            <input type="number" id="co_payment_term_days" min="1" step="1" class="co-input" placeholder="Jumlah hari tempo (contoh: 14)" style="height:44px;background:#fff;">
          </div>

          <div id="coErrorBox" class="co-error-box"></div>

          <button type="button" id="coSubmitBtn" class="co-submit-btn" onclick="submitCheckoutOrder()">
            <span>Bayar sekarang</span>
            <i class="fa-solid fa-arrow-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<dialog id="coPreSubmitNotice" class="co-pre-submit-notice" aria-labelledby="coPreSubmitNoticeTitle">
  <div class="co-pre-submit-notice-card">
    <button type="button" class="co-pre-submit-notice-close" aria-label="Tutup pengingat" onclick="closeCheckoutNotices()">
      <i class="fa-solid fa-xmark" aria-hidden="true"></i>
    </button>
    <div id="coPreSubmitNoticeIcon" class="co-pre-submit-notice-icon" aria-hidden="true">
      <i class="fa-solid fa-circle-info"></i>
    </div>
    <h2 id="coPreSubmitNoticeTitle"></h2>
    <p id="coPreSubmitNoticeMessage"></p>
    <ul id="coPreSubmitNoticeProducts" hidden></ul>
    <button type="button" id="coPreSubmitNoticeContinue" class="co-pre-submit-notice-continue" onclick="continueCheckoutNotices()">
      Oke, lanjutkan
    </button>
  </div>
</dialog>

<dialog id="coReceiptDialog" class="co-receipt-dialog" aria-labelledby="coReceiptDialogTitle" onclick="if(event.target === this) closeReceiptPreview()">
  <div class="co-receipt-dialog-layout">
    <div class="co-receipt-dialog-toolbar">
      <strong id="coReceiptDialogTitle">Struk Pesanan</strong>
      <button type="button" class="co-receipt-dialog-close" onclick="closeReceiptPreview()" aria-label="Tutup pratinjau struk">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i> Tutup
      </button>
    </div>
    <iframe id="coReceiptFrame" class="co-receipt-frame" title="Pratinjau struk pesanan"></iframe>
    <div class="co-receipt-dialog-actions">
      <button type="button" id="coReceiptPrintButton" class="co-receipt-print" onclick="printReceiptPreview()" disabled>
        <i class="fa-solid fa-print" aria-hidden="true"></i> Cetak / Simpan PDF
      </button>
      <button type="button" class="co-receipt-whatsapp" onclick="confirmReceiptViaWhatsApp()">
        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Konfirmasi WhatsApp
      </button>
    </div>
  </div>
</dialog>
@endsection

@section('scripts')
<script>
  const PRODUCT_CATALOG = @json($productCatalog ?? []);
  const FALLBACK_LOGO_URL = '{{ asset("LOGO APOTEK MEDIX.PNG") }}';
  const WA_NUMBER = @json(config('contacts.customer_service.whatsapp'));
  const CART_CONTEXT_KEY = 'apotekmedix_active_cart_context';

  let activeCtx = {
    storageKey: 'apotekmedix_cart_b2c',
    priceChannel: 'b2c',
    wholesaleOrder: false,
    adminPbfOrder: false,
    adminPurchaseOrderBase: null,
    continueShoppingUrl: '{{ route("home") }}'
  };

  try {
    const saved = JSON.parse(localStorage.getItem(CART_CONTEXT_KEY) || '{}');
    if (saved && saved.storageKey) {
      activeCtx = Object.assign({}, activeCtx, saved);
    }
  } catch (e) {}

  const consultationCheckoutPrescription = @json($consultationPrescriptionPayload);
  let checkoutCart = consultationCheckoutPrescription
    ? consultationCheckoutPrescription.items.map((item) => ({
        id: item.id,
        name: item.name,
        brand: item.brand,
        img: item.image,
        qty: item.quantity,
        price: item.price,
        discount: item.discount || 0,
        saleUnit: item.sale_unit || 'box',
        unitLabel: item.unit_label || 'Kemasan',
        note: item.instructions || '',
        consultationPrescriptionNumber: consultationCheckoutPrescription.number,
      }))
    : JSON.parse(localStorage.getItem(activeCtx.storageKey) || '[]');

  if (consultationCheckoutPrescription) {
    activeCtx.storageKey = `apotekmedix_cart_consultation_${consultationCheckoutPrescription.id}`;
    activeCtx.priceChannel = 'b2c';
    activeCtx.wholesaleOrder = false;
    activeCtx.adminPbfOrder = false;
  }

  if (activeCtx.storageKey === 'apotekmedix_cart_b2b' || activeCtx.storageKey === 'apotekmedix_cart_pbf') {
    activeCtx.wholesaleOrder = true;
  }
  if (activeCtx.wholesaleOrder) {
    activeCtx.priceChannel = 'b2b';
  } else if (!activeCtx.priceChannel) {
    activeCtx.priceChannel = 'b2c';
  }
  activeCtx.continueShoppingUrl = activeCtx.adminPbfOrder
    ? @json(route('admin.produk-pbf.index'))
    : (activeCtx.wholesaleOrder ? @json(route('products.pbf')) : @json(route('home')));

  let deliveryMode = 'ship';
  let extraVoucherDiscount = 0;
  let appliedVoucher = null;
  let checkoutNoticeQueue = [];
  let checkoutNoticesAcknowledged = false;
  let checkoutPrescriptionConfirmationToken = @json($prescriptionConfirmationToken);

  function resolveProductImage(item) {
    const catalogEntry = PRODUCT_CATALOG[item.id] || PRODUCT_CATALOG[String(item.id)];
    if (catalogEntry && catalogEntry.image_url) {
      item.img = catalogEntry.image_url;
      return catalogEntry.image_url;
    }
    if (item.img && !String(item.img).includes('LOGO APOTEK MEDIX')) {
      return item.img;
    }
    return item.img || FALLBACK_LOGO_URL;
  }

  function formatIdrDecimal(num) {
    return 'Rp ' + Math.round(Number(num || 0)).toLocaleString('id-ID') + ',00';
  }

  function formatRpPlain(num) {
    return 'Rp ' + Math.round(Number(num || 0)).toLocaleString('id-ID');
  }

  function openReceiptPreview() {
    const order = window.completedOrder;
    const dialog = document.getElementById('coReceiptDialog');
    const frame = document.getElementById('coReceiptFrame');
    if (!order?.invoice_url || !dialog || !frame) {
      document.getElementById('coErrorBox').textContent = 'Tautan struk tidak tersedia. Silakan muat ulang halaman dan coba kembali.';
      document.getElementById('coErrorBox').style.display = 'block';
      return;
    }

    frame.src = order.invoice_url;
    const printButton = document.getElementById('coReceiptPrintButton');
    printButton.disabled = true;
    frame.onload = () => {
      printButton.disabled = false;
    };
    document.getElementById('coReceiptDialogTitle').textContent = `Struk Pesanan #${order.id}`;
    if (!dialog.open) dialog.showModal();
  }

  function closeReceiptPreview() {
    document.getElementById('coReceiptDialog')?.close();
  }

  function printReceiptPreview() {
    const frame = document.getElementById('coReceiptFrame');
    if (!frame?.contentWindow) {
      document.getElementById('coErrorBox').textContent = 'Struk belum selesai dimuat. Silakan coba kembali.';
      document.getElementById('coErrorBox').style.display = 'block';
      return;
    }

    frame.contentWindow.focus();
    frame.contentWindow.print();
  }

  function confirmReceiptViaWhatsApp() {
    closeReceiptPreview();
    openWhatsAppCheckout();
  }

  function escapeHtml(text) {
    return String(text || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function getCheckoutRedLogoItems() {
    if (consultationCheckoutPrescription || activeCtx.wholesaleOrder || activeCtx.adminPbfOrder) return [];
    return checkoutCart.filter((item) => {
      const catalogItem = PRODUCT_CATALOG[item.id] || PRODUCT_CATALOG[String(item.id)] || {};
      return item.requiresPrescription === true
        || catalogItem.has_red_logo === true
        || catalogItem.logo_color === 'red';
    });
  }

  function getCheckoutPreSubmitNotices() {
    const notices = [];
    const logoKItems = checkoutPrescriptionConfirmationToken ? [] : getCheckoutRedLogoItems();
    if (logoKItems.length) {
      notices.push({
        type: 'warning',
        prescriptionConfirmation: true,
        title: 'Ada produk Logo K di pesanan',
        message: 'Produk bertanda Logo K memerlukan resep dokter yang sah. Jika Anda memiliki resep, konfirmasikan untuk melanjutkan.',
        products: logoKItems.map((item) => item.name || 'Produk obat'),
      });
    }

    const selectedMethod = document.getElementById('co_payment_method')?.value || '';
    if (!activeCtx.adminPbfOrder && ['QRIS', 'Transfer'].includes(selectedMethod)) {
      notices.push({
        type: 'payment',
        title: 'Pengingat bukti transaksi',
        message: 'Simpan bukti transaksi QRIS atau transfer Anda. Pastikan bukti pembayaran sudah dipilih dan terlampir sebelum pesanan dikirim.',
      });
    }

    return notices;
  }

  function showCheckoutNotice() {
    const notice = checkoutNoticeQueue[0];
    if (!notice) return;

    const dialog = document.getElementById('coPreSubmitNotice');
    const icon = document.getElementById('coPreSubmitNoticeIcon');
    const products = document.getElementById('coPreSubmitNoticeProducts');
    const continueButton = document.getElementById('coPreSubmitNoticeContinue');
    document.getElementById('coPreSubmitNoticeTitle').textContent = notice.title;
    document.getElementById('coPreSubmitNoticeMessage').textContent = notice.message;
    continueButton.textContent = notice.prescriptionConfirmation
      ? 'Saya memiliki resep, lanjutkan'
      : 'Oke, lanjutkan';
    icon.classList.toggle('is-warning', notice.type === 'warning');
    icon.innerHTML = notice.type === 'warning'
      ? '<i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>'
      : '<i class="fa-solid fa-receipt" aria-hidden="true"></i>';

    while (products.firstChild) products.removeChild(products.firstChild);
    if (notice.products?.length) {
      notice.products.forEach((name) => {
        const item = document.createElement('li');
        item.textContent = name;
        products.appendChild(item);
      });
      products.hidden = false;
    } else {
      products.hidden = true;
    }

    if (!dialog.open) dialog.showModal();
  }

  function closeCheckoutNotices() {
    document.getElementById('coPreSubmitNotice')?.close();
    checkoutNoticeQueue = [];
  }

  function continueCheckoutNotices() {
    checkoutNoticeQueue.shift();
    if (checkoutNoticeQueue.length) {
      showCheckoutNotice();
      return;
    }

    document.getElementById('coPreSubmitNotice')?.close();
    checkoutNoticesAcknowledged = true;
    submitCheckoutOrder();
  }

  function persistCheckoutCart() {
    checkoutNoticesAcknowledged = false;
    localStorage.setItem(activeCtx.storageKey, JSON.stringify(checkoutCart));
    extraVoucherDiscount = 0;
    appliedVoucher = null;
    const discountInput = document.getElementById('coDiscountCodeInput');
    const discountMessage = document.getElementById('coDiscountMsgBox');
    if (discountInput) discountInput.value = '';
    if (discountMessage) discountMessage.style.display = 'none';
  }

  function updateCheckoutQuantity(index, value) {
    const item = checkoutCart[index];
    if (!item || consultationCheckoutPrescription) return;

    const parsedQuantity = Number.parseInt(value, 10);
    let quantity = Number.isFinite(parsedQuantity) ? Math.max(1, parsedQuantity) : 1;
    const availableQuantity = Number(item.availableQty);
    if (Number.isFinite(availableQuantity) && availableQuantity > 0) {
      quantity = Math.min(quantity, availableQuantity);
    }

    item.qty = quantity;
    item.discount = Math.min(Number(item.discount || 0), Number(item.price || 0) * quantity);
    persistCheckoutCart();
    renderCheckoutSummary();
  }

  function removeCheckoutItem(index) {
    if (consultationCheckoutPrescription || !checkoutCart[index]) return;

    checkoutCart.splice(index, 1);
    persistCheckoutCart();
    renderCheckoutSummary();
  }

  function renderCheckoutSummary() {
    const itemsContainer = document.getElementById('coSummaryItems');
    const countEl = document.getElementById('coTotalItemsCount');
    const subEl = document.getElementById('coSubtotalDisplay');
    const discRow = document.getElementById('coDiscountRow');
    const discEl = document.getElementById('coDiscountDisplay');
    const grandEl = document.getElementById('coGrandTotalDisplay');

    if (!checkoutCart.length) {
      itemsContainer.innerHTML = `<div style="padding:1rem 0;color:#64748b;font-size:0.9rem;">Keranjang belanja kosong. <a href="{{ route('cart.index') }}" style="color:#0E7490;font-weight:700;text-decoration:underline;">Kembali ke keranjang</a></div>`;
      countEl.textContent = '0';
      subEl.textContent = 'Rp 0,00';
      discRow.style.display = 'none';
      grandEl.textContent = 'Rp 0,00';
      const badge = document.getElementById('cartBadgeNav');
      if (badge) {
        badge.textContent = '0';
        badge.style.display = 'none';
      }
      const cashAmount = document.getElementById('coCashAmount');
      if (cashAmount) cashAmount.textContent = 'Rp 0,00';
      return;
    }

    let html = '';
    let subtotal = 0;
    let itemDiscounts = 0;
    let totalQty = 0;
    let updatedCatalogImg = false;

    checkoutCart.forEach((item) => {
      const prevImg = item.img;
      const imgUrl = resolveProductImage(item);
      if (item.img !== prevImg) updatedCatalogImg = true;
      const catalogEntry = PRODUCT_CATALOG[item.id] || PRODUCT_CATALOG[String(item.id)];
      const logoColor = catalogEntry?.logo_color;
      const logoMarkerHtml = ['green', 'red', 'blue'].includes(logoColor)
        ? `<span class="co-sum-logo-marker co-sum-logo-marker--${logoColor}" role="img" aria-label="Logo ${logoColor === 'green' ? 'hijau' : (logoColor === 'red' ? 'merah' : 'biru')}">${logoColor === 'red' ? 'K' : ''}</span>`
        : '';

      const qty = Number(item.qty || 1);
      const price = Number(item.price || 0);
      const lineSub = price * qty;
      const disc = Number(item.discount || 0);
      const unit = item.saleUnit || 'box';
      const unitLabel = item.unitLabel || item.unitLabels?.[unit] || '';
      const availableQuantity = Number(item.availableQty);
      const hasStockLimit = Number.isFinite(availableQuantity) && availableQuantity >= 0;
      const hasUsableStockLimit = hasStockLimit && availableQuantity > 0;
      const itemActionsHtml = consultationCheckoutPrescription
        ? ''
        : `<div class="co-sum-actions">
            <div class="co-sum-quantity" aria-label="Atur jumlah ${escapeHtml(item.name)}">
              <button type="button" aria-label="Kurangi jumlah" onclick="updateCheckoutQuantity(${checkoutCart.indexOf(item)}, ${qty - 1})" ${qty <= 1 ? 'disabled' : ''}>−</button>
              <input type="number" min="1" ${hasUsableStockLimit ? `max="${availableQuantity}"` : ''} step="1" value="${qty}" aria-label="Jumlah ${escapeHtml(item.name)}" onchange="updateCheckoutQuantity(${checkoutCart.indexOf(item)}, this.value)">
              <button type="button" aria-label="Tambah jumlah" onclick="updateCheckoutQuantity(${checkoutCart.indexOf(item)}, ${qty + 1})" ${hasStockLimit && qty >= availableQuantity ? 'disabled' : ''}>+</button>
            </div>
            <button type="button" class="co-sum-remove" aria-label="Hapus ${escapeHtml(item.name)}" title="Hapus produk" onclick="removeCheckoutItem(${checkoutCart.indexOf(item)})">
              <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
            </button>
          </div>`;

      subtotal += lineSub;
      itemDiscounts += disc;
      totalQty += qty;

      const thumbHtml = `<img src="${escapeHtml(imgUrl)}" alt="${escapeHtml(item.name)}" onerror="this.onerror=null;this.src='${escapeHtml(FALLBACK_LOGO_URL)}';">`;

      html += `
        <div class="co-sum-item">
          <div class="co-sum-left">
            <div class="co-sum-thumb">
              ${thumbHtml}
              ${logoMarkerHtml}
            </div>
            <div>
              <div class="co-sum-name">${escapeHtml(item.name)}</div>
              ${unitLabel ? `<div class="co-sum-meta">${escapeHtml(unitLabel)}${item.note ? ' &middot; ' + escapeHtml(item.note) : ''}</div>` : ''}
              <div class="co-sum-detail">${qty} x ${formatIdrDecimal(price)}</div>
              ${itemActionsHtml}
            </div>
          </div>
          <div class="co-sum-price">${formatIdrDecimal(lineSub)}</div>
        </div>
      `;
    });

    if (updatedCatalogImg) {
      localStorage.setItem(activeCtx.storageKey, JSON.stringify(checkoutCart));
    }

    const totalDiscount = Math.min(subtotal, itemDiscounts + extraVoucherDiscount);
    const grandTotal = Math.max(0, subtotal - totalDiscount);

    itemsContainer.innerHTML = html;
    countEl.textContent = totalQty;
    subEl.textContent = formatIdrDecimal(subtotal);

    const badge = document.getElementById('cartBadgeNav');
    if (badge) {
      badge.textContent = totalQty;
      badge.style.display = totalQty > 0 ? 'flex' : 'none';
    }

    if (totalDiscount > 0) {
      discRow.style.display = 'flex';
      discEl.textContent = '-' + formatIdrDecimal(totalDiscount);
    } else {
      discRow.style.display = 'none';
    }

    grandEl.textContent = formatIdrDecimal(grandTotal);
    const cashAmount = document.getElementById('coCashAmount');
    if (cashAmount) cashAmount.textContent = formatIdrDecimal(grandTotal);
  }

  async function applyCheckoutDiscount() {
    const inp = document.getElementById('coDiscountCodeInput');
    const btn = document.getElementById('coDiscountApplyBtn');
    const msgBox = document.getElementById('coDiscountMsgBox');
    const val = (inp?.value || '').trim().toUpperCase();

    if (!val) {
      extraVoucherDiscount = 0;
      appliedVoucher = null;
      if (msgBox) msgBox.style.display = 'none';
      renderCheckoutSummary();
      return;
    }

    const subtotalAfterItemDisc = checkoutCart.reduce((s, i) => {
      const sub = Number(i.price || 0) * Number(i.qty || 1);
      const disc = Number(i.discount || 0);
      return s + Math.max(0, sub - disc);
    }, 0);

    if (btn) {
      btn.disabled = true;
      btn.textContent = '...';
    }

    try {
      const res = await fetch('{{ route("checkout.validate-discount") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          code: val,
          subtotal: subtotalAfterItemDisc
        })
      });

      const data = await res.json();
      if (!res.ok || !data.valid) {
        extraVoucherDiscount = 0;
        appliedVoucher = null;
        if (msgBox) {
          msgBox.style.display = 'block';
          msgBox.style.background = '#fef2f2';
          msgBox.style.border = '1px solid #fecaca';
          msgBox.style.color = '#991b1b';
          msgBox.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${escapeHtml(data.message || 'Kode diskon tidak valid.')}`;
        }
        renderCheckoutSummary();
        return;
      }

      appliedVoucher = data;
      extraVoucherDiscount = Number(data.discount_amount || 0);
      if (msgBox) {
        const quotaInfo = data.is_unlimited
          ? 'Tanpa batas kuota'
          : `Sisa kuota: ${data.remaining_quota} user`;
        msgBox.style.display = 'block';
        msgBox.style.background = '#ecfdf5';
        msgBox.style.border = '1px solid #a7f3d0';
        msgBox.style.color = '#065f46';
        msgBox.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${escapeHtml(data.message)} <span style="font-weight:500;color:#047857;">(${quotaInfo})</span>`;
      }
      renderCheckoutSummary();
    } catch (e) {
      if (msgBox) {
        msgBox.style.display = 'block';
        msgBox.style.background = '#fef2f2';
        msgBox.style.border = '1px solid #fecaca';
        msgBox.style.color = '#991b1b';
        msgBox.textContent = 'Gagal memeriksa kode diskon.';
      }
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.textContent = 'Pakai';
      }
    }
  }

  function setDeliveryMode(mode) {
    deliveryMode = mode;
    document.getElementById('tabShipBtn').classList.toggle('active', mode === 'ship');
    document.getElementById('tabPickupBtn').classList.toggle('active', mode === 'pickup');
    document.getElementById('coShippingAddressFields').style.display = mode === 'ship' ? '' : 'none';
    document.getElementById('coPickupLocationBox').style.display = mode === 'pickup' ? 'flex' : 'none';
    updateShippingMethodStatus();
    updatePaymentProofRequirement();
  }

  function updateShippingMethodStatus() {
    const summaryShip = document.getElementById('coShippingSummaryText');
    if (!summaryShip) return;
    if (deliveryMode === 'pickup') {
      summaryShip.textContent = 'Ambil di Apotek (Gratis)';
      return;
    }
    const addr = document.getElementById('co_address')?.value.trim() || '';
    if (addr) {
      summaryShip.textContent = 'Dihitung saat konfirmasi';
    } else {
      summaryShip.textContent = 'Masukkan alamat pengiriman';
    }
  }

  function onBuyerTypeChange() {
    onPaymentMethodChange();
  }

  function selectPaymentMethod(method) {
    if (method === 'Tempo' && !isTempoAvailable()) return;
    checkoutNoticesAcknowledged = false;
    const input = document.getElementById('co_payment_method');
    if (input) input.value = method;
    document.querySelectorAll('#coPayOptionsGrid .co-pay-opt').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.payMethod === method);
    });
    onPaymentMethodChange();
  }

  function onPaymentMethodChange() {
    const payMethod = document.getElementById('co_payment_method')?.value || 'QRIS';
    const tempoWrap = document.getElementById('coTempoDaysWrap');
    if (tempoWrap) {
      tempoWrap.style.display = payMethod === 'Tempo' ? 'block' : 'none';
    }
    document.getElementById('coQrisDetails').hidden = payMethod !== 'QRIS';
    document.getElementById('coTransferDetails').hidden = payMethod !== 'Transfer';
    document.getElementById('coCashDetails').hidden = payMethod !== 'Cash';
    updatePaymentProofRequirement();
  }

  let paymentProofPreviewUrl = null;

  function clearPaymentProof() {
    const proofInput = document.getElementById('coPaymentProof');
    const proofUpload = document.getElementById('coProofUpload');
    const proofPreview = document.getElementById('coProofPreview');
    const proofFileName = document.getElementById('coProofFileName');
    const removeButton = document.getElementById('coProofRemove');
    if (proofInput) proofInput.value = '';
    if (proofPreview) {
      proofPreview.removeAttribute('src');
      proofPreview.style.display = 'none';
    }
    if (proofFileName) proofFileName.textContent = '';
    if (proofUpload) proofUpload.classList.remove('has-file');
    if (removeButton) removeButton.style.display = 'none';
    if (paymentProofPreviewUrl) {
      URL.revokeObjectURL(paymentProofPreviewUrl);
      paymentProofPreviewUrl = null;
    }
  }

  function handlePaymentProofChange() {
    const proofInput = document.getElementById('coPaymentProof');
    const selectedFile = proofInput?.files?.[0];
    if (!selectedFile) return;

    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
    const errorBox = document.getElementById('coErrorBox');
    if (!allowedTypes.includes(selectedFile.type) || selectedFile.size > 5 * 1024 * 1024) {
      clearPaymentProof();
      errorBox.textContent = selectedFile.size > 5 * 1024 * 1024
        ? 'Ukuran bukti pembayaran maksimal 5 MB.'
        : 'Format bukti pembayaran harus JPG, PNG, WEBP, atau PDF.';
      errorBox.style.display = 'block';
      return;
    }

    errorBox.style.display = 'none';
    const proofUpload = document.getElementById('coProofUpload');
    const proofPreview = document.getElementById('coProofPreview');
    const proofFileName = document.getElementById('coProofFileName');
    const removeButton = document.getElementById('coProofRemove');
    proofUpload?.classList.add('has-file');
    if (proofFileName) proofFileName.textContent = selectedFile.name;
    if (removeButton) removeButton.style.display = 'inline-flex';
    if (paymentProofPreviewUrl) URL.revokeObjectURL(paymentProofPreviewUrl);
    paymentProofPreviewUrl = null;

    if (selectedFile.type.startsWith('image/')) {
      paymentProofPreviewUrl = URL.createObjectURL(selectedFile);
      if (proofPreview) {
        proofPreview.src = paymentProofPreviewUrl;
        proofPreview.style.display = 'block';
      }
    } else if (proofPreview) {
      proofPreview.removeAttribute('src');
      proofPreview.style.display = 'none';
    }
  }

  function updatePaymentProofRequirement() {
    const proofWrap = document.getElementById('coPaymentProofWrap');
    const proofInput = document.getElementById('coPaymentProof');
    if (!proofWrap || !proofInput) return;
    const selectedMethod = document.getElementById('co_payment_method')?.value || '';
    const requiresProof = !activeCtx.adminPbfOrder
      && ['QRIS', 'Transfer'].includes(selectedMethod);
    proofWrap.hidden = !requiresProof;
    proofInput.required = requiresProof;
    if (!requiresProof) clearPaymentProof();
  }

  function isTempoAvailable() {
    return Boolean(activeCtx.wholesaleOrder || activeCtx.adminPbfOrder);
  }

  function configureTempoOption() {
    const tempoOption = document.getElementById('coTempoOption');
    const disabledLabel = document.getElementById('coTempoDisabledLabel');
    if (!tempoOption) return;
    const available = isTempoAvailable();
    tempoOption.disabled = !available;
    tempoOption.setAttribute('aria-disabled', String(!available));
    if (disabledLabel) {
      disabledLabel.hidden = available;
      disabledLabel.style.display = available ? 'none' : 'block';
    }
    if (!available && document.getElementById('co_payment_method')?.value === 'Tempo') {
      selectPaymentMethod(@json($defaultCheckoutPaymentMethod));
    }
  }

  function onBillingModeChange() {
    const mode = document.querySelector('input[name="billing_addr_mode"]:checked')?.value || 'same';
    document.getElementById('radioBillSameRow')?.classList.toggle('active', mode === 'same');
    document.getElementById('radioBillDiffRow')?.classList.toggle('active', mode === 'different');
    const customFields = document.getElementById('coBillingCustomFields');
    if (customFields) customFields.style.display = mode === 'different' ? 'block' : 'none';
  }

  async function submitCheckoutOrder() {
    const errBox = document.getElementById('coErrorBox');
    const submitBtn = document.getElementById('coSubmitBtn');
    errBox.style.display = 'none';

    if (!checkoutCart.length) {
      errBox.textContent = 'Keranjang Anda masih kosong.';
      errBox.style.display = 'block';
      return;
    }

    if (!checkoutNoticesAcknowledged) {
      checkoutNoticeQueue = getCheckoutPreSubmitNotices();
      if (checkoutNoticeQueue.length) {
        showCheckoutNotice();
        return;
      }
    }

    const redLogoItems = getCheckoutRedLogoItems();
    if (redLogoItems.length && !checkoutPrescriptionConfirmationToken) {
      const confirmationButton = document.getElementById('coPreSubmitNoticeContinue');
      if (confirmationButton) confirmationButton.disabled = true;
      try {
        const response = await fetch(@json(route('checkout.confirm-prescription')), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': @json(csrf_token()),
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            product_ids: [...new Set(redLogoItems.map((item) => Number(item.id)))]
          })
        });
        const result = await response.json();
        if (!response.ok) {
          throw new Error(result.message || 'Konfirmasi resep tidak dapat disimpan.');
        }
        if (!result.confirmation_token) {
          throw new Error('Token konfirmasi resep tidak diterima. Silakan coba kembali.');
        }
        checkoutPrescriptionConfirmationToken = result.confirmation_token;
        return submitCheckoutOrder();
      } catch (error) {
        errBox.textContent = error instanceof Error ? error.message : 'Konfirmasi resep tidak dapat disimpan. Silakan coba kembali.';
        errBox.style.display = 'block';
        return;
      } finally {
        if (confirmationButton) confirmationButton.disabled = false;
      }
    }

    const jenis = activeCtx.adminPbfOrder
      ? 'apotik'
      : (activeCtx.wholesaleOrder || activeCtx.priceChannel === 'b2b' ? 'pbf' : 'umum');
    void ((activeCtx.adminPbfOrder || activeCtx.wholesaleOrder
      ? 'apotik'
      : 'umum'));

    const fullName = document.getElementById('co_full_name').value.trim();
    const phoneVal = document.getElementById('co_phone').value.trim();

    const originalTotal = checkoutCart.reduce((s, it) => s + (Number(it.price) * Number(it.qty)), 0);
    const itemDiscounts = checkoutCart.reduce((s, it) => s + (Number(it.discount) || 0), 0);
    const totalDiscount = Math.min(originalTotal, itemDiscounts + extraVoucherDiscount);
    const discountedTotal = Math.max(0, originalTotal - totalDiscount);

    const paymentMethod = document.getElementById('co_payment_method').value;
    const paymentTermDays = Number(document.getElementById('co_payment_term_days')?.value || 0);
    const paymentProof = document.getElementById('coPaymentProof')?.files?.[0] || null;
    const requiresPaymentProof = !activeCtx.adminPbfOrder
      && ['QRIS', 'Transfer'].includes(paymentMethod);

    const mappedItems = checkoutCart.map((it) => ({
      id: it.id,
      nama_obat: it.name,
      quantity: Number(it.qty || 1),
      harga: Number(it.price || 0),
      sale_unit: it.saleUnit || 'box',
      catatan: it.note || '',
      potongan: Number(it.discount || 0)
    }));
    const consultationPrescriptionNumber = checkoutCart
      .find(it => it.consultationPrescriptionNumber)?.consultationPrescriptionNumber || null;

    const payload = {
      buyer_type: jenis,
      price_channel: activeCtx.wholesaleOrder ? 'b2b' : (activeCtx.priceChannel || 'b2c'),
      prescription_confirmation_token: checkoutPrescriptionConfirmationToken,
      consultation_prescription_number: consultationPrescriptionNumber,
      items: mappedItems,
      total: discountedTotal,
      original_total: originalTotal,
      discounted_total: discountedTotal,
      discount_code: appliedVoucher ? appliedVoucher.code : null,
      approval_status: 'pending',
      payment_method: paymentMethod,
      payment_term_days: paymentMethod === 'Tempo' ? paymentTermDays : null,
      transaction_channel: 'online',
      delivery_method: deliveryMode === 'pickup' ? 'pickup' : 'delivery',
      is_pbf_purchase_order: Boolean(activeCtx.adminPbfOrder)
    };

    if (activeCtx.adminPbfOrder) {
      payload.pbf_name = document.getElementById('co_pbf_name').value.trim();
      payload.pbf_address = document.getElementById('co_pbf_address').value.trim();
      payload.pbf_phone = document.getElementById('co_pbf_phone').value.trim();
      payload.purchase_order_type = document.getElementById('co_pbf_order_type').value;
      payload.purchase_order_profile = document.getElementById('co_pbf_profile').value;
      payload.buyer_name = 'Apotek Medix';
      payload.phone = '08159111911';
      payload.address = 'ITC Cempaka Mas LT.1 No.88, Jakarta Pusat';
      payload.requester_name = document.getElementById('co_signer_name').value.trim();
      payload.apj = document.getElementById('co_signer_title').value.trim();
      payload.sia = document.getElementById('co_signer_sia').value.trim();
      payload.sipa = document.getElementById('co_signer_sipa').value.trim();
      if (!payload.pbf_name || !payload.pbf_address || !payload.pbf_phone) {
        errBox.textContent = 'Nama PBF, alamat PBF, dan nomor telepon PBF wajib diisi.';
        errBox.style.display = 'block';
        return;
      }
    } else {
      const addressVal = document.getElementById('co_address').value.trim();
      const billingMode = document.querySelector('input[name="billing_addr_mode"]:checked')?.value || 'same';
      const billingNameVal = billingMode === 'different'
        ? (document.getElementById('co_billing_name')?.value.trim() || '')
        : '';
      const billingAddrVal = billingMode === 'different'
        ? (document.getElementById('co_billing_address')?.value.trim() || '')
        : '';
      const billingPhoneVal = billingMode === 'different'
        ? (document.getElementById('co_billing_phone')?.value.trim() || '')
        : '';
      const baseAddress = deliveryMode === 'pickup' ? 'Ambil di Apotek Medix' : addressVal;
      const billingDetails = [
        billingNameVal,
        billingAddrVal,
        billingPhoneVal ? `Telp: ${billingPhoneVal}` : ''
      ].filter(Boolean).join(', ');

      payload.buyer_name = fullName;
      payload.phone = phoneVal;
      payload.address = billingDetails ? `${baseAddress} (Alamat Penagihan: ${billingDetails})` : baseAddress;

      if (billingMode === 'different' && (!billingNameVal || !billingAddrVal)) {
        errBox.textContent = 'Nama lengkap dan alamat penagihan wajib diisi jika memakai alamat penagihan lain.';
        errBox.style.display = 'block';
        return;
      }

      if (jenis === 'pbf' || jenis === 'apotik' || jenis === 'dokter' || activeCtx.wholesaleOrder) {
        payload.requester_name = fullName;
        const outletVal = document.getElementById('co_b2b_outlet')?.value.trim() || '';
        payload.outlet_name = outletVal;
        payload.buyer_name = outletVal;
        payload.sia = document.getElementById('co_b2b_sia')?.value.trim() || '';
        payload.sipa = document.getElementById('co_b2b_sipa')?.value.trim() || '';
        if (!outletVal || !fullName || !phoneVal || !payload.sia || !payload.sipa) {
          errBox.textContent = 'Nama outlet, Nama Apoteker/Dokter, No. Telp, SIA, dan SIPA wajib diisi.';
          errBox.style.display = 'block';
          return;
        }
      }

      if (!payload.buyer_name || !payload.phone) {
        errBox.textContent = 'Nama lengkap dan nomor HP wajib diisi.';
        errBox.style.display = 'block';
        return;
      }
      if ((consultationCheckoutPrescription || deliveryMode === 'ship') && !addressVal) {
        errBox.textContent = 'Alamat pengiriman wajib diisi.';
        errBox.style.display = 'block';
        return;
      }
    }

    if (requiresPaymentProof && !paymentProof) {
      errBox.textContent = 'Unggah bukti pembayaran QRIS atau transfer sebelum melanjutkan.';
      errBox.style.display = 'block';
      document.getElementById('coPaymentProof')?.focus();
      return;
    }

    if (paymentMethod === 'Tempo' && (!Number.isInteger(paymentTermDays) || paymentTermDays < 1)) {
      errBox.textContent = 'Jumlah hari tempo wajib diisi untuk metode pembayaran Tempo.';
      errBox.style.display = 'block';
      return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Memproses pesanan...</span> <i class="fa-solid fa-spinner fa-spin"></i>';

    try {
      const formData = new FormData();
      const appendFormData = (key, value) => {
        if (value === null || value === undefined) return;
        if (typeof value === 'boolean') {
          formData.append(key, value ? '1' : '0');
          return;
        }
        if (Array.isArray(value)) {
          value.forEach((entry, index) => {
            if (entry && typeof entry === 'object') {
              Object.entries(entry).forEach(([childKey, childValue]) => appendFormData(`${key}[${index}][${childKey}]`, childValue));
            } else {
              appendFormData(`${key}[${index}]`, entry);
            }
          });
          return;
        }
        formData.append(key, String(value));
      };
      Object.entries(payload).forEach(([key, value]) => appendFormData(key, value));
      if (requiresPaymentProof && paymentProof) formData.append('payment_proof', paymentProof);

      const response = await fetch('{{ route("orders.history.store") }}', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        body: formData
      });

      if (!response.ok) {
        const errJson = await response.json();
        throw (errJson.errors || new Error(errJson.message || 'Gagal menyimpan pesanan.'));
      }

      const result = await response.json();
      window.completedOrder = {
        ...payload,
        id: result.id,
        invoice_url: result.invoice_url,
        payment_proof_url: result.payment_proof_url || null,
        cartSnapshot: [...checkoutCart]
      };

      localStorage.setItem(activeCtx.storageKey, '[]');

      document.getElementById('coOrderNumberLabel').textContent = `ID Pesanan #${result.id}`;
      if (requiresPaymentProof) {
        document.getElementById('coSuccessDesc').textContent = 'Bukti pembayaran berhasil diunggah. Buka struk, lalu cetak atau simpan sebagai PDF sebelum konfirmasi melalui WhatsApp.';
      } else if (payload.delivery_method === 'delivery') {
        document.getElementById('coSuccessDesc').textContent = 'Pesanan kirim Anda telah tercatat. Buka struk untuk mencetak atau menyimpannya sebagai PDF, lalu konfirmasi melalui WhatsApp.';
      } else {
        document.getElementById('coSuccessDesc').textContent = 'Pesanan Anda telah tercatat. Buka struk untuk melihat rincian pesanan.';
        document.getElementById('coWhatsappBtn').style.display = 'none';
      }
      document.getElementById('coFormWrapper').style.display = 'none';
      const payRight = document.getElementById('coPaymentRightWrapper');
      if (payRight) payRight.style.display = 'none';
      const editSumLink = document.getElementById('coEditSummaryLink');
      if (editSumLink) editSumLink.style.display = 'none';
      const editCartFooterLink = document.getElementById('coEditCartFooterLink');
      if (editCartFooterLink) editCartFooterLink.style.display = 'none';
      const discInputRow = document.getElementById('coDiscountInputRow');
      if (discInputRow) discInputRow.style.display = 'none';
      const discMsgBox = document.getElementById('coDiscountMsgBox');
      if (discMsgBox) discMsgBox.style.display = 'none';
      const navBadge = document.getElementById('cartBadgeNav');
      if (navBadge) {
        navBadge.textContent = '0';
        navBadge.style.display = 'none';
      }
      const backBtn = document.getElementById('coBackToCatalogBtn');
      if (backBtn) {
        backBtn.href = activeCtx.continueShoppingUrl;
        backBtn.textContent = activeCtx.wholesaleOrder
          ? 'Kembali ke Belanja B2B'
          : (activeCtx.adminPbfOrder ? 'Kembali ke Pesan ke PBF' : 'Kembali ke Beranda');
      }
      document.getElementById('coSuccessPanel').style.display = 'block';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (error) {
      let msg = 'Terjadi kesalahan saat memproses pesanan.';
      if (error && error.message) msg = error.message;
      else if (typeof error === 'object') msg = Object.values(error).map(e => Array.isArray(e) ? e.join(', ') : e).join('<br>');
      errBox.innerHTML = msg;
      errBox.style.display = 'block';
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<span>Bayar sekarang</span> <i class="fa-solid fa-arrow-right"></i>';
    }
  }

  function openWhatsAppCheckout() {
    const ord = window.completedOrder;
    if (!ord) return;
    let total = 0;
    let msg = `*Halo Apotek Medix, saya ingin konfirmasi pesanan #${ord.id}:*\n`;
    (ord.cartSnapshot || []).forEach((it, idx) => {
      const sub = it.price * it.qty;
      const disc = it.discount || 0;
      const afterDisc = sub - disc;
      total += afterDisc;
      const unit = it.saleUnit || 'box';
      const unitLabel = it.unitLabel || it.unitLabels?.[unit] || unit;
      msg += `${idx + 1}. *${it.name}*\n   Qty: ${it.qty} ${unitLabel} x ${formatRpPlain(it.price)} = ${formatRpPlain(sub)}\n`;
    });
    msg += `---\n*Total: ${formatRpPlain(ord.total)}*\n\n*Data Pemesan:*\n`;
    const nowWib = new Intl.DateTimeFormat('id-ID', { timeZone: 'Asia/Jakarta', day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }).format(new Date()).replace(/\./g, ':') + ' WIB';
    const tempoInfo = ord.payment_method === 'Tempo' && ord.payment_term_days ? ` (${ord.payment_term_days} hari)` : '';
    const attachmentInstruction = ord.payment_proof_url
      ? '\n\n*Instruksi:* Saya sudah mengunggah bukti pembayaran pada checkout. Saya juga akan melampirkan PDF struk yang sudah diunduh dan foto bukti transfer langsung di chat ini. Mohon bantu verifikasi pembayaran dan pesanan saya. Terima kasih!'
      : '\n\nMohon bantu proses pesanan saya. Terima kasih!';
    msg += `- Nama: ${ord.buyer_name}\n- Telepon: ${ord.phone}\n- Pengantaran: ${ord.address || 'Ambil di Apotek'}\n- Pembayaran: ${ord.payment_method}${tempoInfo}\n- Waktu Pesan: ${nowWib}${attachmentInstruction}`;
    window.open(`https://wa.me/${WA_NUMBER}?text=${encodeURIComponent(msg)}`, '_blank');
  }

  document.addEventListener('DOMContentLoaded', () => {
    if (consultationCheckoutPrescription) {
      document.getElementById('coDeliveryTabs')?.setAttribute('hidden', 'hidden');
      document.getElementById('coEditSummaryLink')?.setAttribute('hidden', 'hidden');
      document.getElementById('coEditCartFooterLink')?.setAttribute('hidden', 'hidden');
    }
    const consultationPrescriptionNumber = checkoutCart
      .find(item => item.consultationPrescriptionNumber)?.consultationPrescriptionNumber;
    if (consultationPrescriptionNumber) {
      const notice = document.getElementById('consultationPrescriptionNotice');
      notice.textContent = `Resep dokter terlampir · Nomor resep: ${consultationPrescriptionNumber}`;
      notice.style.display = 'block';
    }
    configureTempoOption();
    onPaymentMethodChange();
    document.getElementById('coPaymentProof')?.addEventListener('change', handlePaymentProofChange);
    document.getElementById('coProofRemove')?.addEventListener('click', clearPaymentProof);
    if (activeCtx.adminPbfOrder) {
      document.getElementById('coAdminPbfFields').style.display = 'block';
    } else if (activeCtx.wholesaleOrder || activeCtx.priceChannel === 'b2b') {
      const b2bPermit = document.getElementById('coB2bPermitFields');
      if (b2bPermit) b2bPermit.style.display = 'block';
      const b2bOutlet = document.getElementById('co_b2b_outlet');
      if (b2bOutlet) b2bOutlet.style.display = 'block';
      const fullNameInp = document.getElementById('co_full_name');
      if (fullNameInp) {
        fullNameInp.placeholder = 'Nama Apoteker / Dokter *';
      }
      const phoneInp = document.getElementById('co_phone');
      if (phoneInp) phoneInp.placeholder = 'No. Telp *';
      const addrInp = document.getElementById('co_address');
      if (addrInp) addrInp.placeholder = 'Alamat *';
    }
    updateShippingMethodStatus();
    onBuyerTypeChange();
    renderCheckoutSummary();
  });
</script>
@endsection
