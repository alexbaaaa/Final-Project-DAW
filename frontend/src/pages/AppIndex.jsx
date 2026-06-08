import { useEffect, useMemo, useState } from 'react'

const WEEK_DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']

function AppIndex({ apiBaseUrl, path, session, onLogout, onNavigate }) {
  const [monthDate, setMonthDate] = useState(() => startOfMonth(new Date()))
  const [appData, setAppData] = useState(null)
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState('')
  const [selectedEvent, setSelectedEvent] = useState(null)
  const [isMenuOpen, setIsMenuOpen] = useState(false)

  const monthKey = useMemo(() => formatMonthKey(monthDate), [monthDate])

  useEffect(() => {
    const controller = new AbortController()

    const loadAppData = async () => {
      setIsLoading(true)
      setError('')

      try {
        const params = new URLSearchParams({
          user_id: session.user.id,
          month: monthKey,
        })
        const response = await fetch(`${apiBaseUrl}/front/app-data?${params}`, {
          headers: {
            Accept: 'application/json',
          },
          signal: controller.signal,
        })
        const payload = await response.json()

        if (!response.ok) {
          throw new Error(payload.message || 'Calendar data could not be loaded.')
        }

        setAppData(payload.data)
      } catch (requestError) {
        if (requestError.name !== 'AbortError') {
          setError(requestError.message || 'Calendar data could not be loaded.')
        }
      } finally {
        if (!controller.signal.aborted) {
          setIsLoading(false)
        }
      }
    }

    loadAppData()

    return () => controller.abort()
  }, [apiBaseUrl, monthKey, session.user.id])

  const events = appData?.events || []
  const calendar = appData?.calendar || []
  const linkedSwimmers = getLinkedSwimmers(appData, session)
  const swimmer = appData?.swimmer || linkedSwimmers[0] || session.swimmer
  const categorySummary = formatUniqueList(linkedSwimmers.map((linkedSwimmer) => linkedSwimmer.category))
  const swimmerSummary = formatUniqueList(linkedSwimmers.map((linkedSwimmer) => linkedSwimmer.full_name))
  const calendarDays = useMemo(
    () => buildCalendarDays(monthDate, events, calendar),
    [calendar, events, monthDate],
  )
  const currentSection = getSection(path)

  const goToPreviousMonth = () => {
    setMonthDate((current) => new Date(current.getFullYear(), current.getMonth() - 1, 1))
  }

  const goToNextMonth = () => {
    setMonthDate((current) => new Date(current.getFullYear(), current.getMonth() + 1, 1))
  }

  return (
    <main className="app-index-page">
      <header className="app-header">
        <button className="app-brand" type="button" onClick={() => onNavigate('/app')}>
          Swimming Up
        </button>

        <nav className="app-nav" aria-label="Application navigation">
          <button
            className="nav-menu-button"
            type="button"
            onClick={() => setIsMenuOpen((isOpen) => !isOpen)}
          >
            Menu
          </button>

          {isMenuOpen && (
            <div className="nav-menu">
              <button type="button" onClick={() => handleMenuNavigation('/app/profile')}>
                Profile
              </button>
              <button type="button" onClick={() => handleMenuNavigation('/app/training-group')}>
                Training group
              </button>
              <button type="button" onClick={() => handleMenuNavigation('/app/events')}>
                Event
              </button>
              <button type="button" onClick={() => handleMenuNavigation('/')}>
                Main page
              </button>
              <button className="logout-item" type="button" onClick={onLogout}>
                Log out
              </button>
            </div>
          )}
        </nav>
      </header>

      {currentSection === 'calendar' && (
        <section className="calendar-layout" aria-labelledby="calendar-title">
          <div className="calendar-panel">
            <div className="calendar-toolbar">
              <div>
                <p className="home-eyebrow">Club calendar</p>
                <h1 id="calendar-title">{formatMonthTitle(monthDate)}</h1>
                <p>
                  {categorySummary
                    ? `Events for ${categorySummary}`
                    : 'No swimmer profile is linked to this user yet.'}
                </p>
              </div>

              <div className="month-actions">
                <button type="button" onClick={goToPreviousMonth}>
                  Previous
                </button>
                <button type="button" onClick={goToNextMonth}>
                  Next
                </button>
              </div>
            </div>

            {isLoading && <p className="calendar-feedback">Loading calendar...</p>}
            {error && <p className="calendar-feedback calendar-feedback-error">{error}</p>}

            {!isLoading && !error && (
              <div className="calendar-grid" role="grid" aria-label={formatMonthTitle(monthDate)}>
                {WEEK_DAYS.map((weekDay) => (
                  <div className="week-day" key={weekDay}>
                    {weekDay}
                  </div>
                ))}

                {calendarDays.map((day) => (
                  <article
                    className={day.className}
                    key={day.key}
                    role="gridcell"
                    aria-label={day.label}
                  >
                    {day.date && (
                      <>
                        <span className="day-number">{day.date.getDate()}</span>
                        {day.specialDay && (
                          <span className="day-state">{getCalendarTypeLabel(day.specialDay.day_type)}</span>
                        )}
                        <div className="day-events">
                          {day.events.map((event) => (
                            <button
                              className="event-chip"
                              key={event.id}
                              type="button"
                              onClick={() => setSelectedEvent(event)}
                            >
                              {event.event_name}
                            </button>
                          ))}
                        </div>
                      </>
                    )}
                  </article>
                ))}
              </div>
            )}
          </div>

          <aside className="calendar-legend" aria-label="Calendar legend">
            <h2>Legend</h2>
            <span><i className="legend-event" /> Club event</span>
            <span><i className="legend-holiday-training" /> Holiday with training</span>
            <span><i className="legend-holiday-no-training" /> Holiday without training</span>
            <span><i className="legend-suspended" /> Training suspended</span>
          </aside>
        </section>
      )}

      {currentSection === 'profile' && (
        <InfoPage
          eyebrow="Profile"
          title={swimmer?.full_name || session.user.full_name}
          lines={[
            `User type: ${formatUserType(session.user.user_type)}`,
            `Alias: ${session.user.alias || 'Not assigned'}`,
            `Linked swimmers: ${swimmerSummary || 'Not linked'}`,
            `Categories: ${categorySummary || 'Not linked'}`,
          ]}
        />
      )}

      {currentSection === 'training' && (
        <InfoPage
          eyebrow="Training group"
          title={categorySummary || 'No training group'}
          lines={
            linkedSwimmers.length
              ? linkedSwimmers.map((linkedSwimmer) => `${linkedSwimmer.full_name} - ${linkedSwimmer.category}`)
              : ['This user has no linked swimmer profile yet.']
          }
        />
      )}

      {currentSection === 'events' && (
        <InfoPage
          eyebrow="Events"
          title="Current month events"
          lines={events.length ? events.map((event) => `${event.event_date} - ${event.event_name}`) : ['No events found for this month.']}
        />
      )}

      {selectedEvent && (
        <div className="event-modal" role="dialog" aria-modal="true" aria-labelledby="event-title">
          <div className="event-modal-card">
            <button className="modal-close" type="button" onClick={() => setSelectedEvent(null)}>
              Close
            </button>
            <p className="home-eyebrow">{selectedEvent.event_date}</p>
            <h2 id="event-title">{selectedEvent.event_name}</h2>
            <p>{selectedEvent.description}</p>
            <strong>Categories</strong>
            <span>{selectedEvent.categories}</span>
          </div>
        </div>
      )}
    </main>
  )

  function handleMenuNavigation(nextPath) {
    setIsMenuOpen(false)
    onNavigate(nextPath)
  }
}

function InfoPage({ eyebrow, lines, title }) {
  return (
    <section className="info-page" aria-labelledby="info-title">
      <p className="home-eyebrow">{eyebrow}</p>
      <h1 id="info-title">{title}</h1>
      <div className="info-lines">
        {lines.map((line) => (
          <p key={line}>{line}</p>
        ))}
      </div>
    </section>
  )
}

function buildCalendarDays(monthDate, events, calendar) {
  const year = monthDate.getFullYear()
  const month = monthDate.getMonth()
  const firstDay = new Date(year, month, 1)
  const daysInMonth = new Date(year, month + 1, 0).getDate()
  const leadingDays = (firstDay.getDay() + 6) % 7
  const cells = []
  const eventsByDate = groupByDate(events, 'event_date')
  const calendarByDate = groupByDate(calendar, 'date')

  for (let index = 0; index < leadingDays; index += 1) {
    cells.push({
      className: 'calendar-day calendar-day-empty',
      date: null,
      key: `empty-start-${index}`,
      label: 'Empty day',
    })
  }

  for (let day = 1; day <= daysInMonth; day += 1) {
    const date = new Date(year, month, day)
    const dateKey = formatDateKey(date)
    const dayEvents = eventsByDate[dateKey] || []
    const specialDay = (calendarByDate[dateKey] || [])[0]
    const classes = ['calendar-day']

    if (dayEvents.length > 0) {
      classes.push('calendar-day-event')
    }

    if (specialDay) {
      classes.push(`calendar-day-${specialDay.day_type.replace(/_/g, '-')}`)
    }

    cells.push({
      className: classes.join(' '),
      date,
      events: dayEvents,
      key: dateKey,
      label: `${dateKey}${dayEvents.length ? `, ${dayEvents.length} event` : ''}`,
      specialDay,
    })
  }

  while (cells.length % 7 !== 0) {
    cells.push({
      className: 'calendar-day calendar-day-empty',
      date: null,
      key: `empty-end-${cells.length}`,
      label: 'Empty day',
    })
  }

  return cells
}

function groupByDate(records, dateField) {
  return records.reduce((groupedRecords, record) => {
    const dateKey = record[dateField]

    if (!groupedRecords[dateKey]) {
      groupedRecords[dateKey] = []
    }

    groupedRecords[dateKey].push(record)

    return groupedRecords
  }, {})
}

function formatDateKey(date) {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(
    date.getDate(),
  ).padStart(2, '0')}`
}

function formatMonthKey(date) {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`
}

function formatMonthTitle(date) {
  return new Intl.DateTimeFormat('en-US', {
    month: 'long',
    year: 'numeric',
  }).format(date)
}

function formatUserType(userType) {
  const labels = {
    legal_guardian: 'Legal guardian',
    swimmer: 'Swimmer',
  }

  return labels[userType] || userType || 'Undefined'
}

function getLinkedSwimmers(appData, session) {
  if (appData?.swimmers?.length) {
    return appData.swimmers
  }

  if (session.swimmers?.length) {
    return session.swimmers
  }

  const singleSwimmer = appData?.swimmer || session.swimmer

  return singleSwimmer ? [singleSwimmer] : []
}

function formatUniqueList(values) {
  return [...new Set(values.filter(Boolean))].join(', ')
}

function getCalendarTypeLabel(dayType) {
  const labels = {
    holiday_training: 'Holiday training',
    holiday_no_training: 'Holiday off',
    training_suspended: 'Training suspended',
  }

  return labels[dayType] || dayType
}

function getSection(path) {
  if (path === '/app/profile') {
    return 'profile'
  }

  if (path === '/app/training-group') {
    return 'training'
  }

  if (path === '/app/events') {
    return 'events'
  }

  return 'calendar'
}

function startOfMonth(date) {
  return new Date(date.getFullYear(), date.getMonth(), 1)
}

export default AppIndex
