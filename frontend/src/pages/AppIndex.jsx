import { useEffect, useMemo, useState } from 'react'

const WEEK_DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
const TRAINING_WEEK_DAYS = [
  { label: 'Mon', value: 'monday' },
  { label: 'Tue', value: 'tuesday' },
  { label: 'Wed', value: 'wednesday' },
  { label: 'Thu', value: 'thursday' },
  { label: 'Fri', value: 'friday' },
  { label: 'Sat', value: 'saturday' },
  { label: 'Sun', value: 'sunday' },
]
const TRAINING_HOURS = Array.from({ length: 18 }, (_, index) => index + 6)
const TRAINING_BLOCKING_CALENDAR_TYPES = new Set(['holiday_no_training', 'training_suspended'])

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
  const trainingGroup = appData?.training_group || null
  const linkedSwimmers = getLinkedSwimmers(appData, session)
  const swimmer = appData?.swimmer || linkedSwimmers[0] || session.swimmer
  const categorySummary = formatUniqueList(linkedSwimmers.map((linkedSwimmer) => linkedSwimmer.category))
  const swimmerSummary = formatUniqueList(linkedSwimmers.map((linkedSwimmer) => linkedSwimmer.full_name))
  const calendarDays = useMemo(
    () => buildCalendarDays(monthDate, events, calendar),
    [calendar, events, monthDate],
  )
  const trainingWeekDays = useMemo(() => buildTrainingWeekDays(new Date()), [])
  const trainingWeekItems = useMemo(
    () => buildTrainingWeekItems(trainingGroup, events, calendar, trainingWeekDays),
    [calendar, events, trainingGroup, trainingWeekDays],
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
        <TrainingGroupPage
          categorySummary={categorySummary}
          error={error}
          isLoading={isLoading}
          trainingGroup={trainingGroup}
          weekDays={trainingWeekDays}
          weekItems={trainingWeekItems}
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

function TrainingGroupPage({ categorySummary, error, isLoading, trainingGroup, weekDays, weekItems }) {
  const hasSchedule = Array.isArray(trainingGroup?.schedule) && trainingGroup.schedule.length > 0
  const hasWeekItems = weekDays.some((day) => (weekItems[day.dateKey] || []).length > 0)

  return (
    <section className="training-group-page" aria-labelledby="training-group-title">
      <header className="training-group-hero">
        <p className="home-eyebrow">Training group</p>
        <h1 id="training-group-title">{trainingGroup?.name || 'No training group'}</h1>
        <p>{trainingGroup?.categories || categorySummary || 'No category linked to this profile.'}</p>
      </header>

      <section className="training-week-section" aria-labelledby="training-week-title">
        <div className="training-week-heading">
          <div>
            <p className="home-eyebrow">Schedule</p>
            <h2 id="training-week-title">Current week</h2>
          </div>
          <span>{formatTrainingWeekRange(weekDays)}</span>
        </div>

        {isLoading && <p className="training-week-feedback">Loading training group...</p>}
        {!isLoading && error && <p className="training-week-feedback training-week-error">{error}</p>}
        {!isLoading && !error && !trainingGroup && (
          <p className="training-week-feedback">No training group is linked to this swimmer category yet.</p>
        )}
        {!isLoading && !error && trainingGroup && !hasSchedule && !hasWeekItems && (
          <p className="training-week-feedback">This training group has no schedule blocks yet.</p>
        )}

        {!isLoading && !error && trainingGroup && (
          <div className="training-week-scroll">
            <div className="training-week-calendar" aria-label="Training group weekly schedule">
              <div className="training-week-corner" />
              {weekDays.map((day) => (
                <div className="training-day-header" key={day.dateKey}>
                  <span>{day.label}</span>
                  <strong>{day.date.getDate()}</strong>
                </div>
              ))}

              <div className="training-time-column">
                {TRAINING_HOURS.map((hour) => (
                  <div className="training-time-label" key={hour}>
                    {formatHour(hour)}
                  </div>
                ))}
              </div>

              {weekDays.map((day) => (
                <div className="training-day-column" key={day.dateKey}>
                  {TRAINING_HOURS.map((hour) => (
                    <div className="training-hour-slot" key={`${day.dateKey}-${hour}`} />
                  ))}
                  {(weekItems[day.dateKey] || []).map((item) => (
                    <div
                      className={`training-week-block ${item.className}`}
                      key={item.id}
                      style={getTrainingBlockStyle(item)}
                    >
                      <strong>{item.title}</strong>
                      <span>{item.subtitle}</span>
                    </div>
                  ))}
                </div>
              ))}
            </div>
          </div>
        )}
      </section>
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

function buildTrainingWeekDays(baseDate) {
  const weekStart = startOfWeek(baseDate)

  return TRAINING_WEEK_DAYS.map((weekDay, index) => {
    const date = new Date(weekStart)
    date.setDate(weekStart.getDate() + index)

    return {
      date,
      dateKey: formatDateKey(date),
      label: weekDay.label,
      value: weekDay.value,
    }
  })
}

function buildTrainingWeekItems(trainingGroup, events, calendar, weekDays) {
  const weekItems = weekDays.reduce((items, day) => {
    items[day.dateKey] = []

    return items
  }, {})
  const blockedDateKeys = new Set()

  addBlockingCalendarItems(weekItems, blockedDateKeys, calendar, weekDays)
  addBlockingEventItems(weekItems, blockedDateKeys, events, weekDays)
  addTrainingScheduleItems(weekItems, trainingGroup, weekDays, blockedDateKeys)
  addEventItems(weekItems, events, weekDays)
  addCalendarItems(weekItems, calendar)

  Object.keys(weekItems).forEach((dateKey) => {
    weekItems[dateKey] = stackTrainingWeekItems(weekItems[dateKey])
  })

  return weekItems
}

function addTrainingScheduleItems(weekItems, trainingGroup, weekDays, blockedDateKeys) {
  const schedule = Array.isArray(trainingGroup?.schedule) ? trainingGroup.schedule : []

  schedule.forEach((scheduleBlock, index) => {
    const blockDays = Array.isArray(scheduleBlock.days) ? scheduleBlock.days.map(normalizeWeekDay) : []
    const startHour = parseHour(scheduleBlock.from)
    const endHour = parseHour(scheduleBlock.to)

    if (!blockDays.length || startHour === null || endHour === null || endHour <= startHour) {
      return
    }

    weekDays
      .filter((day) => blockDays.includes(day.value) && !blockedDateKeys.has(day.dateKey))
      .forEach((day) => {
        weekItems[day.dateKey].push({
          id: `training-${index}-${day.dateKey}`,
          className: 'training-block-training',
          endHour,
          startHour,
          subtitle: `${formatHour(startHour)} - ${formatHour(endHour)}`,
          title: 'Training',
        })
      })
  })
}

function addBlockingCalendarItems(weekItems, blockedDateKeys, calendar, weekDays) {
  calendar
    .filter((calendarDay) => TRAINING_BLOCKING_CALENDAR_TYPES.has(calendarDay.day_type))
    .forEach((calendarDay) => {
      const weekDay = weekDays.find((day) => day.dateKey === calendarDay.date)

      if (!weekDay) {
        return
      }

      blockedDateKeys.add(weekDay.dateKey)
      weekItems[weekDay.dateKey].push({
        id: `calendar-blocker-${calendarDay.id}-${weekDay.dateKey}`,
        className: `training-block-calendar training-block-${calendarDay.day_type.replace(/_/g, '-')}`,
        endHour: 24,
        startHour: 6,
        subtitle: 'Full day',
        title: calendarDay.title || getCalendarTypeLabel(calendarDay.day_type),
      })
    })
}

function addBlockingEventItems(weekItems, blockedDateKeys, events, weekDays) {
  events
    .filter(isCompetitionEvent)
    .forEach((event) => {
      getWeekDaysForEvent(event, weekDays).forEach((day) => {
        blockedDateKeys.add(day.dateKey)
        weekItems[day.dateKey].push({
          id: `event-blocker-${event.id}-${day.dateKey}`,
          className: 'training-block-event training-block-competition',
          endHour: 24,
          startHour: 6,
          subtitle: 'Full day',
          title: event.event_name || 'Competition',
        })
      })
    })
}

function addEventItems(weekItems, events, weekDays) {
  events
    .filter((event) => !isCompetitionEvent(event))
    .forEach((event) => {
      getWeekDaysForEvent(event, weekDays).forEach((day) => {
        const hours = getDayScopeHours(event.day_scope)

        weekItems[day.dateKey].push({
          id: `event-${event.id}-${day.dateKey}`,
          className: 'training-block-event',
          endHour: hours.end,
          startHour: hours.start,
          subtitle: formatDayScope(event.day_scope),
          title: event.event_name || 'Event',
        })
      })
    })
}

function addCalendarItems(weekItems, calendar) {
  calendar
    .filter((calendarDay) => !TRAINING_BLOCKING_CALENDAR_TYPES.has(calendarDay.day_type))
    .forEach((calendarDay) => {
      if (!weekItems[calendarDay.date]) {
        return
      }

      const hours = getDayScopeHours(calendarDay.day_scope)

      weekItems[calendarDay.date].push({
        id: `calendar-${calendarDay.id}-${calendarDay.date}`,
        className: `training-block-calendar training-block-${calendarDay.day_type.replace(/_/g, '-')}`,
        endHour: hours.end,
        startHour: hours.start,
        subtitle: formatDayScope(calendarDay.day_scope),
        title: calendarDay.title || getCalendarTypeLabel(calendarDay.day_type),
      })
    })
}

function getWeekDaysForEvent(event, weekDays) {
  const startDate = parseDate(event.event_start_date || event.event_date)
  const endDate = parseDate(event.event_end_date || event.event_start_date || event.event_date) || startDate

  if (!startDate || !endDate) {
    return []
  }

  return weekDays.filter((day) => day.date >= startDate && day.date <= endDate)
}

function isCompetitionEvent(event) {
  return String(event.event_type || '').toLowerCase() === 'competition'
}

function stackTrainingWeekItems(items) {
  return [...items]
    .sort((firstItem, secondItem) => firstItem.startHour - secondItem.startHour || firstItem.endHour - secondItem.endHour)
    .map((item, index, sortedItems) => {
      const overlappingItems = sortedItems.filter(
        (candidate) => candidate.startHour < item.endHour && candidate.endHour > item.startHour,
      )
      const stackIndex = overlappingItems.findIndex((candidate) => candidate.id === item.id)
      const stackTotal = Math.max(overlappingItems.length, 1)

      return {
        ...item,
        stackIndex: stackIndex === -1 ? index : stackIndex,
        stackTotal,
      }
    })
}

function getTrainingBlockStyle(item) {
  const top = getHourPosition(item.startHour)
  const bottom = getHourPosition(item.endHour)
  const width = 100 / item.stackTotal
  const left = item.stackIndex * width

  return {
    height: `${Math.max(bottom - top, 4)}%`,
    left: `calc(${left}% + 4px)`,
    top: `${top}%`,
    width: `calc(${width}% - 8px)`,
  }
}

function getDayScopeHours(dayScope) {
  if (dayScope === 'morning') {
    return { start: 6, end: 14 }
  }

  if (dayScope === 'afternoon') {
    return { start: 14, end: 24 }
  }

  return { start: 6, end: 24 }
}

function getHourPosition(hour) {
  return ((Math.min(Math.max(hour, 6), 24) - 6) / 18) * 100
}

function parseHour(value) {
  const [rawHour, rawMinutes = '0'] = String(value || '').split(':')
  const hour = Number.parseInt(rawHour, 10)
  const minutes = Number.parseInt(rawMinutes, 10)

  if (Number.isNaN(hour) || Number.isNaN(minutes)) {
    return null
  }

  return hour + minutes / 60
}

function formatHour(value) {
  const hour = Math.floor(value)
  const minutes = Math.round((value - hour) * 60)

  return `${String(hour).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`
}

function normalizeWeekDay(value) {
  const normalizedValue = String(value || '')
    .trim()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
  const labels = {
    1: 'monday',
    2: 'tuesday',
    3: 'wednesday',
    4: 'thursday',
    5: 'friday',
    6: 'saturday',
    7: 'sunday',
    friday: 'friday',
    lunes: 'monday',
    monday: 'monday',
    martes: 'tuesday',
    miercoles: 'wednesday',
    sabado: 'saturday',
    saturday: 'saturday',
    sunday: 'sunday',
    jueves: 'thursday',
    thursday: 'thursday',
    tuesday: 'tuesday',
    viernes: 'friday',
    wednesday: 'wednesday',
  }

  return labels[normalizedValue] || normalizedValue
}

function parseDate(value) {
  if (!value) {
    return null
  }

  const [dateValue] = String(value).split('T')
  const [year, month, day] = dateValue.split('-').map(Number)

  if (!year || !month || !day) {
    return null
  }

  return new Date(year, month - 1, day)
}

function startOfWeek(date) {
  const weekStart = new Date(date.getFullYear(), date.getMonth(), date.getDate())
  const dayOffset = (weekStart.getDay() + 6) % 7
  weekStart.setDate(weekStart.getDate() - dayOffset)
  weekStart.setHours(0, 0, 0, 0)

  return weekStart
}

function formatTrainingWeekRange(weekDays) {
  if (!weekDays.length) {
    return ''
  }

  return `${formatDisplayDate(weekDays[0].dateKey)} - ${formatDisplayDate(weekDays[weekDays.length - 1].dateKey)}`
}

function formatDisplayDate(value) {
  const date = parseDate(value)

  if (!date) {
    return value || 'date not available'
  }

  return new Intl.DateTimeFormat('en-US', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(date)
}

function formatDayScope(dayScope) {
  const labels = {
    afternoon: 'Afternoon only',
    full_day: 'Full day',
    morning: 'Morning only',
  }

  return labels[dayScope] || dayScope || 'Full day'
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
