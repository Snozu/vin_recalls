import { render } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { useState } from 'react';

import SearchBar from './components/SearchBar';
import RecallList from './components/RecallList';
import ErrorMessage from './components/ErrorMessage';
import LoadingSpinner from './components/LoadingSpinner';
import './style.css';

const App = () => {
    const [vin, setVin] = useState('');
    const [message, setMessage] = useState('');
    const [hasRecall, setHasRecall] = useState(false);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const handleSearch = (searchVin) => {
        setLoading(true);
        setError(null);
        setVin(searchVin);
        setMessage('');

        apiFetch({ path: `/vin-recalls/v1/search?vin=${searchVin}` })
            .then((data) => {
                if (data.message) {
                    setMessage(data.message);
                    setHasRecall(data.hasRecall);
                } else {
                    setMessage('');
                    setHasRecall(false);
                }
                setLoading(false);
            })
            .catch((error) => {
                setError(error.message);
                setLoading(false);
            });
    };

    return (
        <div className="vin-recalls-container">
            <h2>{__('BÚSQUEDA DE VIN/HIN DEL VEHÍCULO', 'vin-recalls')}</h2>
            <SearchBar onSearch={handleSearch} loading={loading} />
            {loading && <LoadingSpinner />}
            <ErrorMessage message={error} />
            <RecallList message={message} hasRecall={hasRecall} />
        </div>
    );
};

render(<App />, document.getElementById('vin-recalls-react-app'));