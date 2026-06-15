function ProfileSelect({ onLogout, onProfileSelected, session }) {
  const swimmers = getSessionSwimmers(session)
  const userName = session?.user?.full_name || session?.user?.alias || 'Swimming Up user'

  return (
    <main className="profile-select-page">
      <div className="pool-scene" aria-hidden="true">
        <span className="lane lane-1" />
        <span className="lane lane-2" />
        <span className="lane lane-3" />
        <span className="lane lane-4" />
        <span className="wake wake-1" />
        <span className="wake wake-2" />
      </div>

      <button className="back-link" type="button" onClick={onLogout}>
        Log out
      </button>

      <section className="profile-select-shell" aria-labelledby="profile-select-title">
        <p className="home-eyebrow">Swimming Up</p>
        <h1 id="profile-select-title">Which profile do you want to view?</h1>
        <p className="profile-select-copy">{userName}</p>

        {swimmers.length > 0 ? (
          <div className="profile-card-grid">
            {swimmers.map((swimmer) => (
              <button
                className="profile-card"
                key={swimmer.id}
                type="button"
                onClick={() => onProfileSelected(swimmer)}
              >
                <span className="profile-card-mark" aria-hidden="true">
                  {getInitials(swimmer.full_name)}
                </span>
                <span className="profile-card-content">
                  <strong>{swimmer.full_name}</strong>
                  <span>{swimmer.category || 'No category assigned'}</span>
                </span>
              </button>
            ))}
          </div>
        ) : (
          <p className="profile-select-empty">No swimmer profiles are linked to this account yet.</p>
        )}
      </section>
    </main>
  )
}

function getSessionSwimmers(session) {
  if (session?.swimmers?.length) {
    return session.swimmers
  }

  return session?.swimmer ? [session.swimmer] : []
}

function getInitials(fullName) {
  return String(fullName || 'SU')
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase()
}

export default ProfileSelect
