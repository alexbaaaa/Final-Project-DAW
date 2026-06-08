import { useEffect, useMemo, useState } from 'react'
import './App.css'
import AppIndex from './pages/AppIndex.jsx'
import Home from './pages/Home.jsx'
import Login from './pages/Login.jsx'

const SESSION_KEY = 'swimmingUpSession'

function App() {
  const [path, setPath] = useState(window.location.pathname)
  const [session, setSession] = useState(() => readSession())

  const apiBaseUrl = useMemo(() => {
    const baseUrl = import.meta.env.VITE_API_BASE_URL || '/api'

    return baseUrl.replace(/\/$/, '')
  }, [])

  useEffect(() => {
    const handlePopState = () => {
      setPath(window.location.pathname)
    }

    window.addEventListener('popstate', handlePopState)

    return () => window.removeEventListener('popstate', handlePopState)
  }, [])

  const navigate = (nextPath) => {
    window.history.pushState({}, '', nextPath)
    setPath(nextPath)
  }

  const handleLogin = (nextSession) => {
    window.localStorage.setItem(SESSION_KEY, JSON.stringify(nextSession))
    setSession(nextSession)
    navigate('/app')
  }

  const handleLogout = () => {
    window.localStorage.removeItem(SESSION_KEY)
    setSession(null)
    navigate('/')
  }

  const normalizedPath = path.replace(/\/$/, '') || '/'

  if (normalizedPath === '/login') {
    return (
      <Login
        apiBaseUrl={apiBaseUrl}
        onBack={() => navigate('/')}
        onLogin={handleLogin}
      />
    )
  }

  if (normalizedPath.startsWith('/app')) {
    if (!session) {
      return (
        <Login
          apiBaseUrl={apiBaseUrl}
          onBack={() => navigate('/')}
          onLogin={handleLogin}
        />
      )
    }

    return (
      <AppIndex
        apiBaseUrl={apiBaseUrl}
        path={normalizedPath}
        session={session}
        onLogout={handleLogout}
        onNavigate={navigate}
      />
    )
  }

  return <Home onStart={() => navigate(session ? '/app' : '/login')} />
}

function readSession() {
  try {
    const rawSession = window.localStorage.getItem(SESSION_KEY)

    return rawSession ? JSON.parse(rawSession) : null
  } catch {
    return null
  }
}

export default App
