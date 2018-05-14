/* Fill track 1 to obtain 19 */
jQuery('input[type="radio"]').prop('checked', true);
jQuery('input[type="submit"]').click();

/* Fill track 1 to obtain 100 */
var key = [4, 2, 2, 4, 3, 3, 2, 3, 4, 4,
    1, 3, 1, 4, 2, 3, 1, 1, 5, 4,
    2, 2, 4, 3, 4, 3, 1, 1, 1, 3,
    1, 2, 3, 2, 1, 2, 3, 4, 3, 4,
    1, 2, 2, 4, 2, 4, 3, 2, 3, 1,
    2, 2, 4, 1, 2, 1, 5, 4, 2, 5,
    5, 1, 5, 3, 1, 4, 4, 1, 3, 5,
    4, 2, 1, 2, 4, 5, 1, 1, 3, 4,
    4, 2, 1, 5, 4, 2, 4, 4, 1, 4,
    2, 2, 3, 4, 3, 3, 3, 2, 4, 5];
for (var i = 1; i <= key.length; i++) {
    jQuery('#choice_2_' + i + '_' + (key[i - 1] - 1)).prop('checked', true);
}
jQuery('input[type="submit"]').click();

/* Fill track 2 and track 3 */
jQuery('textarea').text('Test');
jQuery('input[type="submit"]').click();