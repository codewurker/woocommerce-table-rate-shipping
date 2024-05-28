/**
 * Block Notices
 *
 * This file is responsible for rendering Abort Messages from the Table Rate Shipping plugin.
 *
 * @package WooCommerce_Table_Rate_Shipping
 */

const { useSelect }                                         = window.wp.data;
const { registerPlugin }                                    = window.wp.plugins;
const { ExperimentalOrderShippingPackages, StoreNotice }    = window.wc.blocksCheckout;
const { RawHTML }                                           = window.wp.element;

/**
 * Create a store notice component.
 *
 * @param notice
 * @param index
 * @param type
 * @returns {JSX.Element}
 */
const createStoreNotice = ( notice, index, type = 'info' ) => {
    if ( 'debug' === type ) {
        type = 'info';
    }

    const message = <RawHTML>{notice}</RawHTML>;

    return (
        <StoreNotice key={index} status={type} isDismissible={false}>
            {message}
        </StoreNotice>
    );
};

/**
 * Utility function to get the abort message for the current package hashes.
 *
 * @param messages
 * @param packageHashes
 * @returns {string|null}
 */
const getAbortMessageForCurrentPackage = ( messages, packageHashes ) => {
    if ( !messages || !packageHashes ) {
        return null;
    }

    for ( const hash of packageHashes ) {
        if ( messages[hash] ) {
            return messages[hash];
        }
    }
    return null;
};

/**
 * Notices component.
 *
 * @param messages
 * @param packageHashes
 * @returns {JSX.Element}
 * @constructor
 */
const Notices = ({ messages, packageHashes }) => {
    const currentMessage = getAbortMessageForCurrentPackage(messages, packageHashes);

    if ( !currentMessage ) {
        return null;
    }

    return (
        <div className="woocommerce-table-rate-shipping-block-notices">
            {createStoreNotice( currentMessage, 0, 'info' )}
        </div>
    );
};

const render = () => {
    const { abortMessages, hasShippingRates, packageHashes } = useSelect((select) => {
        const storeCartData     = select( 'wc/store/cart' ).getCartData();
        const shippingRates     = storeCartData?.shippingRates || [];
        const hasShippingRates  = shippingRates.some( rate => rate.shipping_rates.length > 0 );
        const abortMessages     = storeCartData?.extensions?.['woocommerce_table_rate_shipping']?.abort_messages;
        const packageHashes     = storeCartData?.extensions?.['woocommerce_table_rate_shipping']?.package_hashes;

        return {
            abortMessages,
            hasShippingRates,
            packageHashes,
        };
    }, []);

    // Ensure we only show abort messages if no shipping rates are available.
    if (hasShippingRates) {
        return null;
    }

    return (
        <ExperimentalOrderShippingPackages>
            <Notices messages={abortMessages} packageHashes={packageHashes} />
        </ExperimentalOrderShippingPackages>
    );
};

registerPlugin('woocommerce-trs-abort-notices', {
    render,
    scope: 'woocommerce-checkout',
});