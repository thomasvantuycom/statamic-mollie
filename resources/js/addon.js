import PaymentAmount from './components/fieldtypes/PaymentAmount.vue';
import PaymentConfig from './components/fieldtypes/PaymentConfig.vue';
import PaymentStatus from './components/fieldtypes/PaymentStatus.vue';
import PaymentSummary from './components/fieldtypes/PaymentSummary.vue';
import PaymentSummaryIndex from './components/fieldtypes/PaymentSummaryIndex.vue';

Statamic.booting(() => {
    Statamic.component('payment_amount-fieldtype', PaymentAmount);
    Statamic.component('payment_config-fieldtype', PaymentConfig);
    Statamic.component('payment_status-fieldtype', PaymentStatus);
    Statamic.component('payment_summary-fieldtype', PaymentSummary);
    Statamic.component('payment_summary-fieldtype-index', PaymentSummaryIndex);
});
