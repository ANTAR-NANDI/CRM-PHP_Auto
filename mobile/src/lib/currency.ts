/** Display pharmacy money as whole Bangladeshi taka; paisa is not used in POS. */
export function roundTaka(value: number | string | null | undefined): number {
  return Math.round(Number(value) || 0);
}

export function formatTaka(value: number | string | null | undefined): string {
  return roundTaka(value).toLocaleString('en-US');
}
