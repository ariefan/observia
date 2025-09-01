/**
 * Format number as Indonesian Rupiah currency
 * @param amount - The amount to format
 * @returns Formatted currency string (e.g., "Rp1.000.000")
 */
export const formatRupiah = (amount: number): string => {
  // Convert to integer (remove decimals)
  const roundedAmount = Math.round(amount);
  
  // Handle zero case
  if (roundedAmount === 0) return 'Rp0';
  
  // Convert to string and add dot separators manually for perfect control
  const numStr = Math.abs(roundedAmount).toString();
  const reversed = numStr.split('').reverse();
  const withDots = [];
  
  for (let i = 0; i < reversed.length; i++) {
    if (i > 0 && i % 3 === 0) {
      withDots.push('.');
    }
    withDots.push(reversed[i]);
  }
  
  const formatted = withDots.reverse().join('');
  return (roundedAmount < 0 ? '-Rp' : 'Rp') + formatted;
};

/**
 * Format number with up to 2 decimal places using Indonesian locale
 * Hides .00 decimals when they are zero (e.g., "123" instead of "123,00")
 * @param amount - The amount to format  
 * @returns Formatted number string (e.g., "1.234,56" or "123")
 */
export const formatQuantity = (amount: number): string => {
  // Check if the number has meaningful decimal places
  const hasDecimals = amount % 1 !== 0;
  
  return new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: hasDecimals ? 2 : 0,
    maximumFractionDigits: 2,
  }).format(amount);
};

/**
 * Alternative Rupiah formatter if Intl.NumberFormat doesn't work properly
 * @param amount - The amount to format
 * @returns Formatted currency string (e.g., "Rp1.000.000")
 */
export const formatRupiahManual = (amount: number): string => {
  const roundedAmount = Math.round(amount);
  
  // Convert to string and add dot separators manually
  const numStr = roundedAmount.toString();
  const reversed = numStr.split('').reverse();
  const withDots = [];
  
  for (let i = 0; i < reversed.length; i++) {
    if (i > 0 && i % 3 === 0) {
      withDots.push('.');
    }
    withDots.push(reversed[i]);
  }
  
  return 'Rp' + withDots.reverse().join('');
};