/**
 * Oktoberfest Calendar Component
 */
(function ($) {
    'use strict';

    const OktoberfestCalendar = {
        init: function (options) {
            this.calendarEl = options.container || $('.calendar-wrapper');
            this.startDate = new Date(options.startDate || '2025-09-20');
            this.endDate = new Date(options.endDate || '2025-10-05');
            this.selectedDate = options.selectedDate ? new Date(options.selectedDate) : null;
            this.inputField = options.inputField || $('input[name="selected_date"]');
            this.compact = options.compact || false;
            this.popupElement = options.popupElement || null;

            // If no date is selected or is invalid, default to start date
            if (!this.selectedDate || isNaN(this.selectedDate.getTime()) ||
                this.selectedDate < this.startDate || this.selectedDate > this.endDate) {
                this.selectedDate = new Date(this.startDate);
            }

            // Get the month and day patterns
            this.startMonth = this.startDate.getMonth();
            this.startDay = this.startDate.getDate();
            this.endMonth = this.endDate.getMonth();
            this.endDay = this.endDate.getDate();

            // Initialize with the year from start date
            this.currentYear = this.startDate.getFullYear();

            this.renderCalendar();
            this.bindEvents();
        },

        renderCalendar: function () {
            this.calendarEl.empty();

            // Year navigation
            const yearNav = $('<div class="year-nav"></div>');
            yearNav.append('<span class="prev-year">&#10094;</span>');
            yearNav.append(`<h2>${this.currentYear}</h2>`);
            yearNav.append('<span class="next-year">&#10095;</span>');
            this.calendarEl.append(yearNav);

            // Calculate the date range for this year (same dates as admin range but in current year)
            const yearStart = new Date(this.currentYear, this.startMonth, this.startDay);
            const yearEnd = new Date(this.currentYear, this.endMonth, this.endDay);

            // Get unique months in this range
            const months = new Set();
            let currentDate = new Date(yearStart);

            while (currentDate <= yearEnd) {
                months.add(currentDate.getMonth());
                currentDate.setDate(currentDate.getDate() + 1);
            }

            // Convert to array and sort
            const sortedMonths = Array.from(months).sort((a, b) => a - b);

            // For compact mode, only show first month
            if (this.compact && sortedMonths.length > 0) {
                this.renderMonth(sortedMonths[0], this.currentYear, yearStart, yearEnd);
            } else {
                // Render all months
                for (let month of sortedMonths) {
                    this.renderMonth(month, this.currentYear, yearStart, yearEnd);
                }
            }

            // Update the input field
            this.updateInputField();
        },

        renderMonth: function (month, year, yearStart, yearEnd) {
            const monthContainer = $('<div class="calendar-month"></div>');
            monthContainer.append(`<div class="month-header">${this.getMonthName(month)} ${year}</div>`);

            // Create weekday headers
            const daysHeader = $('<div class="calendar-days"></div>');
            const weekdays = ['S', 'M', 'T', 'W', 'T', 'F', 'S']; // Shorter labels for compact mode
            weekdays.forEach(day => {
                daysHeader.append(`<div>${day}</div>`);
            });
            monthContainer.append(daysHeader);

            // Determine if this date is selectable (within the admin-defined range)
            const isWithinAdminRange = (date) => {
                return date >= this.startDate && date <= this.endDate;
            };

            // Find first and last days to show in this month
            let firstDay = 1;
            let lastDay = new Date(year, month + 1, 0).getDate();

            if (month === yearStart.getMonth()) {
                firstDay = yearStart.getDate();
            }

            if (month === yearEnd.getMonth()) {
                lastDay = yearEnd.getDate();
            }

            // Create dates grid
            const datesGrid = $('<div class="calendar-dates"></div>');

            // First day of week for the first day we want to display
            const firstDayOfWeek = new Date(year, month, firstDay).getDay();

            // Add empty cells for days before the first day
            for (let i = 0; i < firstDayOfWeek; i++) {
                datesGrid.append('<div class="empty"></div>');
            }

            // Add days
            for (let day = firstDay; day <= lastDay; day++) {
                const currentDate = new Date(year, month, day);
                const dateStr = this.formatDate(currentDate);

                // Check if within admin-defined range
                const isInAdminRange = isWithinAdminRange(currentDate);

                // Check if selected
                const isSelected = this.selectedDate &&
                    currentDate.getDate() === this.selectedDate.getDate() &&
                    currentDate.getMonth() === this.selectedDate.getMonth() &&
                    currentDate.getFullYear() === this.selectedDate.getFullYear();

                let classes = [];

                if (isSelected) {
                    classes.push('selected');
                } else if (isInAdminRange) {
                    classes.push('in-range');
                } else {
                    classes.push('disabled');
                }

                const dateCell = $(`<div class="${classes.join(' ')}" data-date="${dateStr}">${day}</div>`);
                datesGrid.append(dateCell);

                // Complete the row with empty cells if needed
                if (day === lastDay && (firstDayOfWeek + (day - firstDay)) % 7 !== 6) {
                    const remainingCells = 7 - ((firstDayOfWeek + (day - firstDay + 1)) % 7);
                    if (remainingCells < 7) {
                        for (let i = 0; i < remainingCells; i++) {
                            datesGrid.append('<div class="empty"></div>');
                        }
                    }
                }
            }

            monthContainer.append(datesGrid);
            this.calendarEl.append(monthContainer);
        },

        formatDate: function (date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },

        getMonthName: function (monthIndex) {
            const months = ['January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'];
            return months[monthIndex];
        },

        updateInputField: function () {
            if (this.selectedDate) {
                this.inputField.val(this.formatDate(this.selectedDate));
                this.inputField.trigger('change');
            }
        },

        bindEvents: function () {
            const self = this;

            // Year navigation - prevent closing popup
            this.calendarEl.find('.prev-year, .next-year').on('click', function (e) {
                e.stopPropagation();

                if ($(this).hasClass('prev-year')) {
                    self.currentYear--;
                } else {
                    self.currentYear++;
                }

                self.renderCalendar();

                // Re-attach event handlers after re-rendering
                self.calendarEl.find('.prev-year, .next-year').on('click', function (e) {
                    e.stopPropagation();
                    if ($(this).hasClass('prev-year')) {
                        self.currentYear--;
                    } else {
                        self.currentYear++;
                    }
                    self.renderCalendar();
                });

                return false; // Prevent event bubbling
            });

            // Date selection
            this.calendarEl.on('click', '.calendar-dates .in-range', function (e) {
                e.stopPropagation();

                $('.calendar-dates div').removeClass('selected');
                $(this).addClass('selected');

                const selectedDate = $(this).data('date');
                self.selectedDate = new Date(selectedDate);
                self.updateInputField();

                return false; // Prevent event bubbling
            });
        }
    };

    // Make globally available
    window.OktoberfestCalendar = OktoberfestCalendar;

})(jQuery);