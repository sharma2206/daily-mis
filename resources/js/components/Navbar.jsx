import { Link, useLocation } from 'react-router-dom';

export default function Navbar({ badge = 'v1.0' }) {
    const { pathname } = useLocation();
    return (
        <nav className="nav">
            <Link to="/dashboard" className="nav-brand">
                <div className="logo">
                    <span className="material-icons-round">health_and_safety</span>
                </div>
                <h1>Hospital MIS</h1>
                <span className="nav-badge">{badge}</span>
            </Link>
            <div className="nav-links">
                <Link to="/dashboard" className={`nav-link${pathname === '/dashboard' ? ' active' : ''}`}>
                    <span className="material-icons-round">analytics</span>Dashboard
                </Link>
                <Link to="/" className={`nav-link${pathname === '/' ? ' active' : ''}`}>
                    <span className="material-icons-round">cloud_upload</span>Upload
                </Link>
            </div>
        </nav>
    );
}
