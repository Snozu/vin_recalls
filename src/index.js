import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import './style.css';

const App = () => {
    const [vin, setVin] = useState('');
    const [recalls, setRecalls] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const handleSearch = () => {
        setLoading(true);
        setError(null);

        fetch(`/wp-json/vin-recalls/v1/search?vin=${vin}`)
            .then(response => response.json())
            .then(data => {
                if (data.recalls) {
                    setRecalls(data.recalls);
                } else {
                    setError('No recalls found for this VIN.');
                }
                setLoading(false);
            })
            .catch(error => {
                setError('An error occurred. Please try again.');
                setLoading(false);
            });
    };

    return (
        <div className="vin-recalls-container">
            <h2>SEARCH BY VEHICLE VIN/HIN</h2>
            <p className="vin-recalls-note">Enter Your Vehicle VIN/HIN</p>
            <div className="vin-recalls-search-box">
                <input 
                    type="text" 
                    value={vin} 
                    onChange={(e) => setVin(e.target.value)} 
                />
                <button onClick={handleSearch} disabled={loading}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                    </svg>
                </button>
            </div>

            <a href="#" className="vin-recalls-link">Where is my VIN/HIN?</a>

            <p className="vin-recalls-note">Note: Your VIN/HIN is required to search all outstanding recalls</p>

            {error && <p className="vin-recalls-error">{error}</p>}

            {recalls.length > 0 && (
                <div className="vin-recalls-results">
                    <h3>Recalls for {vin}</h3>
                    <ul>
                        {recalls.map((recall, index) => (
                            <li key={index}>
                                <strong>Date:</strong> {recall.date} <br />
                                <strong>Description:</strong> {recall.description}
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
}

ReactDOM.render(<App />, document.getElementById('vin-recalls-react-app'));
