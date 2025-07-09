import { __ } from '@wordpress/i18n';

const ErrorMessage = ({ message }) => {
    if (!message) {
        return null;
    }
    return <p className="vin-recalls-error">{message}</p>;
};

export default ErrorMessage;
