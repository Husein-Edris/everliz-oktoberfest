/**
 * Oktoberfest Calendar Component
 */
(function ($) {
    'use strict';
    const OktoberfestCalendar = {
        init: function (options) {
            this.calendarEl = options.container || $('.calendar-wrapper');
            
            // Default start/end dates
            this.startDate = new Date(options.startDate || '2025-09-20');
            this.endDate = new Date(options.endDate || '2025-10-05');
            
            this.selectedDate = options.selectedDate ? new Date(options.selectedDate) : null;
            this.inputField = options.inputField || $('input[name="selected_date"]');
            this.compact = options.compact || false;
            this.popupElement = options.popupElement || null;
            
            // Min/max years for selection
            this.minYear = options.minYear || 2025;
            this.maxYear = options.maxYear || 2028;
            
            // Store the original start and end dates
            this.originalStartDate = new Date(this.startDate);
            this.originalEndDate = new Date(this.endDate);
            
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
            
            // Initialize with the year from start date or current date
            this.currentYear = options.initialYear || this.startDate.getFullYear();
            
            // Validate current year is within allowed range
            if (this.currentYear < this.minYear) this.currentYear = this.minYear;
            if (this.currentYear > this.maxYear) this.currentYear = this.maxYear;

            // Store date ranges from admin settings
            this.oktoberfestDates = options.dateRanges || {};
            
            this.renderCalendar();
            this.bindEvents();
        },
        
        renderCalendar: function () {
            this.calendarEl.empty();
            
            // Year navigation with dynamic classes for disabled state
            const yearNav = $('<div class="year-nav"></div>');
            
            // Add disabled class if at min year
            const prevYearClass = this.currentYear <= this.minYear ? 'prev-year disabled' : 'prev-year';
            yearNav.append(`<span class="${prevYearClass}">&#10094;</span>`);
            
            yearNav.append(`<h2>${this.currentYear}</h2>`);
            
            // Add disabled class if at max year
            const nextYearClass = this.currentYear >= this.maxYear ? 'next-year disabled' : 'next-year';
            yearNav.append(`<span class="${nextYearClass}">&#10095;</span>`);
            
            // Append year navigation directly to the main element
            this.calendarEl.append(yearNav);
            
            // Add the calendar instruction/title below year navigation
            this.calendarEl.append('<p class="calendar-instruction required-field">Choose a reservation date</p>');
            
            // Calendar container to hold the actual calendar
            const calendarContainer = $('<div class="calendar-container"></div>');
            
            // Update date range for the current year
            this.updateDateRangeForYear(this.currentYear);
            
            // Calculate the date range for this year
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
            
            if (this.compact) {
                // Determine which month to show - start with the first month in the range
                let monthToShow = sortedMonths[0];
                
                // If we have a selected date, show its month instead
                if (this.selectedDate && this.selectedDate.getFullYear() === this.currentYear) {
                    monthToShow = this.selectedDate.getMonth();
                    // If this month isn't in our valid range, default to first month
                    if (!sortedMonths.includes(monthToShow)) {
                        monthToShow = sortedMonths[0];
                    }
                }
                
                // Render only one month in compact mode
                this.renderMonth(monthToShow, this.currentYear, yearStart, yearEnd, calendarContainer);
            } else {
                // Render all months (for the booking widget)
                for (let month of sortedMonths) {
                    this.renderMonth(month, this.currentYear, yearStart, yearEnd, calendarContainer);
                }
            }
            
            // Append the calendar container to the main element
            this.calendarEl.append(calendarContainer);
            
            // Update the input field
            this.updateInputField();
        },
        
        updateDateRangeForYear: function(year) {
            // Check if we have specific Oktoberfest dates for this year
            if (this.oktoberfestDates[year]) {
                this.startDate = new Date(this.oktoberfestDates[year].start);
                this.endDate = new Date(this.oktoberfestDates[year].end);
                
                // Update month and day patterns
                this.startMonth = this.startDate.getMonth();
                this.startDay = this.startDate.getDate();
                this.endMonth = this.endDate.getMonth();
                this.endDay = this.endDate.getDate();
            } else if (year >= this.minYear && year <= this.maxYear) {
                // If no specific dates but within allowed years, estimate based on known pattern
                
                // Most Oktoberfests start on the third Saturday of September
                this.startDate = new Date(year, 8, 15); // September 15 as a starting point
                // Find the next Saturday
                while (this.startDate.getDay() !== 6) {
                    this.startDate.setDate(this.startDate.getDate() + 1);
                }
                
                // End date is 16 days later (typical duration)
                this.endDate = new Date(this.startDate);
                this.endDate.setDate(this.startDate.getDate() + 16);
                
                // Update month and day patterns
                this.startMonth = this.startDate.getMonth();
                this.startDay = this.startDate.getDate();
                this.endMonth = this.endDate.getMonth();
                this.endDay = this.endDate.getDate();
            } else {
                // If outside allowed years, make no dates available
                this.startMonth = -1;
                this.startDay = -1;
                this.endMonth = -1;
                this.endDay = -1;
            }
        },
        
        renderMonth: function (month, year, yearStart, yearEnd, parentContainer) {
            const container = parentContainer || this.calendarEl;
            const monthContainer = $('<div class="calendar-month"></div>');
            monthContainer.append(`<div class="month-header">${this.getMonthName(month)} ${year}</div>`);
            
            // Create weekday headers
            const daysHeader = $('<div class="calendar-days"></div>');
            const weekdays = ['S', 'M', 'D', 'M', 'D', 'F', 'S']; // German weekday abbreviations
            weekdays.forEach(day => {
                daysHeader.append(`<div>${day}</div>`);
            });
            monthContainer.append(daysHeader);
            
            // Determine if this date is selectable (within the admin-defined range)
            const isWithinAdminRange = (date) => {
                return date >= yearStart && date <= yearEnd;
            };
            
            // Find first and last days to show in this month
            let firstDay = 1;
            let lastDay = new Date(year, month + 1, 0).getDate();
            if (month === yearStart.getMonth() && year === yearStart.getFullYear()) {
                firstDay = yearStart.getDate();
            }
            if (month === yearEnd.getMonth() && year === yearEnd.getFullYear()) {
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
            container.append(monthContainer);
        },
        
        formatDate: function (date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        
        getMonthName: function (monthIndex) {
            const months = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni',
                'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
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
            
            // Year navigation
            this.calendarEl.on('click', '.prev-year:not(.disabled), .next-year:not(.disabled)', function (e) {
                e.preventDefault();
                e.stopPropagation();
                
                let newYear = self.currentYear;
                if ($(this).hasClass('prev-year')) {
                    newYear--;
                } else {
                    newYear++;
                }
                
                // Check if the new year is within the allowed range
                if (newYear >= self.minYear && newYear <= self.maxYear) {
                    self.currentYear = newYear;
                    self.renderCalendar();
                    
                    // Keep popup open after rendering
                    if (self.popupElement) {
                        self.popupElement.addClass('active');
                    }
                }
                
                return false;
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