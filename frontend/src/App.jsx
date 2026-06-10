import { useEffect, useMemo, useState } from 'react'
import './App.css'
import AppIndex from './pages/AppIndex.jsx'
import Home from './pages/Home.jsx'
import Login from './pages/Login.jsx'
import PasswordChange from './pages/PasswordChange.jsx'
import ProfileSelect from './pages/ProfileSelect.jsx'

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
    persistSession(nextSession)
    setSession(nextSession)
    navigate(getPostAuthPath(nextSession))
  }

  const handlePasswordChanged = (nextSession) => {
    persistSession(nextSession)
    setSession(nextSession)
    navigate(getPostAuthPath(nextSession))
  }

  const handleProfileSelected = (swimmer) => {
    if (!session || !swimmer) {
      return
    }

    const nextSession = {
      ...session,
      selected_swimmer_id: Number(swimmer.id),
      swimmer,
    }

    persistSession(nextSession)
    setSession(nextSession)
    navigate('/app')
  }

  const handlePasswordRequired = () => {
    setSession((currentSession) => {
      if (!currentSession) {
        return currentSession
      }

      const nextSession = {
        ...currentSession,
        requires_password_change: true,
        user: {
          ...currentSession.user,
          must_change_password: true,
        },
      }

      persistSession(nextSession)

      return nextSession
    })

    navigate('/app/change-password')
  }

  const handleLogout = () => {
    window.localStorage.removeItem(SESSION_KEY)
    setSession(null)
    navigate('/')
  }

  const normalizedPath = path.replace(/\/$/, '') || '/'
  const isPasswordChangeRequired = shouldRequirePasswordChange(session)

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

    if (isPasswordChangeRequired) {
      return (
        <PasswordChange
          apiBaseUrl={apiBaseUrl}
          onLogout={handleLogout}
          onPasswordChanged={handlePasswordChanged}
          session={session}
        />
      )
    }

    if (normalizedPath === '/app/select-profile' || shouldRequireProfileSelection(session)) {
      if (isLegalGuardian(session)) {
        return (
          <ProfileSelect
            onLogout={handleLogout}
            onProfileSelected={handleProfileSelected}
            session={session}
          />
        )
      }
    }

    return (
      <AppIndex
        apiBaseUrl={apiBaseUrl}
        path={['/app/change-password', '/app/select-profile'].includes(normalizedPath) ? '/app' : normalizedPath}
        session={session}
        onLogout={handleLogout}
        onNavigate={navigate}
        onPasswordRequired={handlePasswordRequired}
      />
    )
  }

  return <Home onStart={() => navigate(session ? getPostAuthPath(session) : '/login')} />
}

function readSession() {
  try {
    const rawSession = window.localStorage.getItem(SESSION_KEY)

    return rawSession ? JSON.parse(rawSession) : null
  } catch {
    return null
  }
}

function persistSession(nextSession) {
  window.localStorage.setItem(SESSION_KEY, JSON.stringify(nextSession))
}

function shouldRequirePasswordChange(session) {
  return Boolean(session?.requires_password_change || session?.user?.must_change_password)
}

function getPostAuthPath(session) {
  if (shouldRequirePasswordChange(session)) {
    return '/app/change-password'
  }

  if (shouldRequireProfileSelection(session)) {
    return '/app/select-profile'
  }

  return '/app'
}

function shouldRequireProfileSelection(session) {
  return isLegalGuardian(session) && getSessionSwimmers(session).length > 0 && !session?.selected_swimmer_id
}

function isLegalGuardian(session) {
  return session?.user?.user_type === 'legal_guardian'
}

function getSessionSwimmers(session) {
  if (session?.swimmers?.length) {
    return session.swimmers
  }

  return session?.swimmer ? [session.swimmer] : []
}

export default App
