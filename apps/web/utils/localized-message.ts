/**
 * The API returns bilingual messages as "عربي / English". Pick the part that
 * matches the active UI language; return the text unchanged when it is not bilingual.
 */
export function pickLocalizedMessage(message: string, arabic: boolean): string {
  const parts = message.split(' / ')
  if (parts.length < 2) return message
  const [first, ...rest] = parts
  const second = rest.join(' / ')
  const firstIsArabic = /[؀-ۿ]/.test(first)
  const wantFirst = arabic ? firstIsArabic : !firstIsArabic
  return (wantFirst ? first : second).trim()
}
