import React, { useState, useEffect } from 'react';
import LoginForm from './components/LoginForm';
import { login, logout, getCurrentUser, LoginResponse } from './services/authService';
import './App.css';

function App() {
    const [user, setUser] = useState<LoginResponse | null>(null);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const checkAuth = async () => {
            try {
                const userData = await getCurrentUser();
                setUser(userData);
            } catch (err) {
                console.log('Not logged in');
            }
        };

        checkAuth();
    }, []);

    const handleLogin = async (username: string, password: string) => {
        try {
            const userData = await login(username, password);
            setUser(userData);
            setError(null);
        } catch (err) {
            setError('Échec de la connexion. Veuillez vérifier vos identifiants.');
        }
    };

    const handleLogout = async () => {
        try {
            await logout();
            setUser(null);
        } catch (err) {
            console.error('Logout failed:', err);
        }
    };

    return (
        <div className="App">
            {user ? (
                <div className="p-4">
                    <div className="max-w-7xl mx-auto">
                        <div className="flex justify-between items-center">
                            <h1 className="text-2xl font-bold">
                                Bienvenue, {user.user}!
                            </h1>
                            <button
                                onClick={handleLogout}
                                className="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
                            >
                                Se déconnecter
                            </button>
                        </div>
                        <div className="mt-4">
                            <h2 className="text-lg font-semibold">Vos rôles :</h2>
                            <ul className="list-disc list-inside">
                                {user.roles.map((role, index) => (
                                    <li key={index}>{role}</li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </div>
            ) : (
                <>
                    <LoginForm onLogin={handleLogin} />
                    {error && (
                        <div className="text-center mt-4 text-red-600">
                            {error}
                        </div>
                    )}
                </>
            )}
        </div>
    );
}

export default App;
