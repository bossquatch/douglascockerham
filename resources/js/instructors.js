const bindPhoneInputs = (root = document) => {
    root.querySelectorAll('[data-phone-input]').forEach((input) => {
        if (input.dataset.phoneBound) return;
        input.dataset.phoneBound = 'true';

        const format = () => {
            let digits = input.value.replace(/\D/g, '');
            if (digits.length === 11 && digits.startsWith('1')) digits = digits.slice(1);
            digits = digits.slice(0, 10);

            if (digits.length <= 3) input.value = digits;
            else if (digits.length <= 6) input.value = `(${digits.slice(0, 3)}) ${digits.slice(3)}`;
            else input.value = `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6)}`;
        };

        input.addEventListener('input', format);
        format();
    });
};

const intake = document.querySelector('[data-instructor-intake]');

if (intake) {
    const list = intake.querySelector('[data-course-list]');
    const template = document.querySelector('[data-course-template]');
    const addButton = intake.querySelector('[data-add-course]');
    const reuseSubmitter = intake.querySelector('[data-reuse-submitter]');
    const submittedByName = intake.querySelector('[name="submitted_by_name"]');
    const submittedByEmail = intake.querySelector('[name="submitted_by_email"]');
    const instructorName = intake.querySelector('[name="instructor_name"]');
    const instructorEmail = intake.querySelector('[name="instructor_email"]');
    let nextIndex = list.querySelectorAll('[data-course-entry]').length;

    const setInstructorIdentityDisabled = (disabled) => {
        [instructorName, instructorEmail].forEach((field) => {
            field.disabled = disabled;
            field.closest('.field').classList.toggle('is-disabled', disabled);
        });
    };
    const copySubmitter = () => {
        if (!reuseSubmitter.checked) return;
        instructorName.value = submittedByName.value;
        instructorEmail.value = submittedByEmail.value;
        instructorName.dispatchEvent(new Event('input', { bubbles: true }));
    };
    reuseSubmitter.addEventListener('change', () => {
        if (reuseSubmitter.checked) {
            copySubmitter();
        } else {
            instructorName.value = '';
            instructorEmail.value = '';
            instructorName.dispatchEvent(new Event('input', { bubbles: true }));
        }
        setInstructorIdentityDisabled(reuseSubmitter.checked);
    });
    submittedByName.addEventListener('input', () => copySubmitter());
    submittedByEmail.addEventListener('input', () => copySubmitter());
    if (reuseSubmitter.checked) copySubmitter();
    setInstructorIdentityDisabled(reuseSubmitter.checked);

    const updateSummary = () => {
        const entries = [...list.querySelectorAll('[data-course-entry]')];
        entries.forEach((entry, index) => {
            entry.querySelector('[data-course-number]').textContent = `Course ${index + 1}`;
            entry.querySelector('[data-remove-course]').disabled = entries.length === 1;
        });
        intake.querySelector('[data-summary-courses]').textContent = entries.length;
        intake.querySelector('[data-summary-course-label]').textContent = entries.length === 1 ? 'capability' : 'capabilities';
    };

    const bindEntry = (entry) => {
        const courseCombobox = entry.querySelector('[data-course-combobox]');
        const coursePicker = entry.querySelector('[data-course-picker]');
        const courseSearch = entry.querySelector('[data-course-search]');
        const courseToggle = entry.querySelector('[data-course-toggle]');
        const courseMenu = entry.querySelector('[data-course-menu]');
        const courseOptions = [...entry.querySelectorAll('[data-course-option]')];
        const courseEmpty = entry.querySelector('[data-course-empty]');
        const otherCourseFields = [...entry.querySelectorAll('[data-other-course-field]')];
        let activeCourseIndex = -1;

        const openCourseMenu = () => {
            courseMenu.hidden = false;
            courseSearch.setAttribute('aria-expanded', 'true');
        };
        const closeCourseMenu = () => {
            courseMenu.hidden = true;
            courseSearch.setAttribute('aria-expanded', 'false');
            courseSearch.removeAttribute('aria-activedescendant');
            activeCourseIndex = -1;
            courseOptions.forEach((option) => option.classList.remove('is-active'));
        };
        const visibleCourseOptions = () => courseOptions.filter((option) => !option.hidden);
        const filterCourses = (open = true) => {
            const query = courseSearch.value.trim().toLowerCase();
            courseOptions.forEach((option) => {
                option.hidden = query !== '' && !option.textContent.toLowerCase().includes(query);
            });
            courseEmpty.hidden = visibleCourseOptions().length !== 0;
            activeCourseIndex = -1;
            courseOptions.forEach((option) => option.classList.remove('is-active'));
            if (open) openCourseMenu();
        };
        const selectCourse = (option) => {
            coursePicker.value = option.dataset.value;
            courseSearch.value = option.dataset.label;
            courseOptions.forEach((item) => item.setAttribute('aria-selected', item === option ? 'true' : 'false'));
            closeCourseMenu();
            syncCourseFields();
        };
        const moveActiveCourse = (direction) => {
            const visible = visibleCourseOptions();
            if (!visible.length) return;
            activeCourseIndex = (activeCourseIndex + direction + visible.length) % visible.length;
            courseOptions.forEach((option) => option.classList.remove('is-active'));
            const activeOption = visible[activeCourseIndex];
            activeOption.classList.add('is-active');
            if (!activeOption.id) activeOption.id = `${courseMenu.id}-option-${activeCourseIndex}`;
            courseSearch.setAttribute('aria-activedescendant', activeOption.id);
            activeOption.scrollIntoView({ block: 'nearest' });
        };
        const syncCourseFields = () => {
            const isOther = coursePicker.value === 'other';
            otherCourseFields.forEach((field) => {
                field.hidden = !isOther;
                field.querySelector('input').required = isOther;
            });
        };
        const selectedCourse = courseOptions.find((option) => option.dataset.value === coursePicker.value);
        if (selectedCourse) {
            courseSearch.value = selectedCourse.dataset.label;
        }
        courseSearch.addEventListener('focus', () => {
            courseSearch.select();
            filterCourses();
        });
        courseSearch.addEventListener('input', () => {
            coursePicker.value = '';
            courseOptions.forEach((option) => option.setAttribute('aria-selected', 'false'));
            syncCourseFields();
            filterCourses();
        });
        courseSearch.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                if (courseMenu.hidden) filterCourses();
                moveActiveCourse(event.key === 'ArrowDown' ? 1 : -1);
            } else if (event.key === 'Enter' && !courseMenu.hidden) {
                const visible = visibleCourseOptions();
                const activeOption = visible[activeCourseIndex] || (visible.length === 1 ? visible[0] : null);
                if (activeOption) {
                    event.preventDefault();
                    selectCourse(activeOption);
                }
            } else if (event.key === 'Escape') {
                closeCourseMenu();
            }
        });
        courseToggle.addEventListener('click', () => {
            courseSearch.focus();
            courseSearch.value = '';
            coursePicker.value = '';
            filterCourses();
        });
        courseOptions.forEach((option) => {
            option.addEventListener('pointerdown', (event) => {
                event.preventDefault();
                selectCourse(option);
                courseSearch.focus({ preventScroll: true });
            });
            option.addEventListener('click', (event) => {
                if (event.detail === 0) selectCourse(option);
            });
        });
        courseCombobox.addEventListener('focusout', () => {
            requestAnimationFrame(() => {
                if (!courseCombobox.contains(document.activeElement)) closeCourseMenu();
            });
        });
        syncCourseFields();

        entry.querySelector('[data-remove-course]').addEventListener('click', () => {
            if (list.querySelectorAll('[data-course-entry]').length > 1) {
                entry.remove();
                updateSummary();
            }
        });
    };

    list.querySelectorAll('[data-course-entry]').forEach(bindEntry);
    addButton.addEventListener('click', () => {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
        const entry = wrapper.firstElementChild;
        list.append(entry);
        bindEntry(entry);
        updateSummary();
        entry.scrollIntoView({ behavior: 'smooth', block: 'start' });
        entry.querySelector('input').focus({ preventScroll: true });
    });

    const summaries = [
        ['instructor_name', '[data-summary-instructor]', 'Not entered'],
        ['agency', '[data-summary-agency]', 'Not entered'],
        ['county', '[data-summary-county]', 'Not selected'],
    ];
    summaries.forEach(([name, selector, fallback]) => {
        const field = intake.querySelector(`[name="${name}"]`);
        const output = intake.querySelector(selector);
        const sync = () => output.textContent = field.value.trim() || fallback;
        field.addEventListener('input', sync);
        field.addEventListener('change', sync);
        sync();
    });
    intake.querySelector('[data-intake-form]').addEventListener('submit', () => {
        setInstructorIdentityDisabled(false);
    });
    updateSummary();
}

bindPhoneInputs();



