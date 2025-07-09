import './style.css';

// React y ReactDOM son proporcionados como globales por WordPress
const { useState } = React;

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
                    setError('No se encontraron llamadas a revisión para este VIN.');
                }
                setLoading(false);
            })
            .catch(error => {
                setError('Ocurrió un error. Por favor intente nuevamente.');
                setLoading(false);
            });
    };

    return (
        <div className="vin-recalls-container">
            <h2>BÚSQUEDA POR VIN/HIN DEL VEHÍCULO</h2>
            <p className="vin-recalls-note">Ingrese el VIN/HIN de su vehículo</p>
            <div className="vin-recalls-search-box">
                <input 
                    type="text" 
                    value={vin} 
                    onChange={(e) => setVin(e.target.value)} 
                    placeholder=""
                />
                <button onClick={handleSearch} disabled={loading}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" className="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                    </svg>
                </button>
            </div>

            <a href="#" className="vin-recalls-link">¿Dónde está mi VIN/HIN?</a>

            <p className="vin-recalls-note">Nota: Se requiere su VIN/HIN para buscar todas las llamadas a revisión pendientes</p>

            {error && <p className="vin-recalls-error">{error}</p>}

            {recalls.length > 0 && (
                <div className="vin-recalls-results">
                    <h3>Llamadas a revisión para {vin}</h3>
                    <ul>
                        {recalls.map((recall, index) => (
                            <li key={index}>
                                <strong>Fecha:</strong> {recall.date} <br />
                                <strong>Descripción:</strong> {recall.description}
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
}

ReactDOM.render(<App />, document.getElementById('vin-recalls-react-app'));
