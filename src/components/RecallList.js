import { __ } from '@wordpress/i18n';

const RecallList = ({ message, hasRecall }) => {
    if (!message) {
        return null;
    }

    return (
        <div className="vin-recalls-results">
            <div className={`vin-recalls-message ${hasRecall ? 'has-recall' : 'no-recall'}`}>
                {message}
            </div>
        </div>
    );
};

export default RecallList;
