const BANGLADESH_TIME_ZONE = 'Asia/Dhaka';

/** Format API timestamps consistently for the pharmacy, independent of device settings. */
export function formatBangladeshDateTime(value: string | null | undefined): string {
  if (!value || Number.isNaN(new Date(value).getTime())) return '—';

  return new Intl.DateTimeFormat('en-GB', {
    timeZone: BANGLADESH_TIME_ZONE,
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
    second: '2-digit',
    hour12: true,
  }).format(new Date(value));
}

/** Return YYYY-MM-DD in Bangladesh time for report filters. */
export function bangladeshDate(value = new Date()): string {
  const parts = new Intl.DateTimeFormat('en-CA', {
    timeZone: BANGLADESH_TIME_ZONE,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).formatToParts(value);
  const field = (type: Intl.DateTimeFormatPartTypes) => parts.find((part) => part.type === type)?.value;

  return `${field('year')}-${field('month')}-${field('day')}`;
}
