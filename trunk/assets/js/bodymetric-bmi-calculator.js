( function () {
	'use strict';

	var config = window.sbcCalculatorData || {};
	var i18n = config.i18n || {};

	function parseNumber( value ) {
		var normalizedValue = typeof value === 'string' ? value.trim() : '';

		if ( '' === normalizedValue ) {
			return null;
		}

		var parsedValue = parseFloat( normalizedValue );

		return Number.isFinite( parsedValue ) ? parsedValue : false;
	}

	function getCurrentUnit( calculator ) {
		return calculator.getAttribute( 'data-current-unit' ) || calculator.getAttribute( 'data-default-unit' ) || 'metric';
	}

	function getField( calculator, fieldName ) {
		return calculator.querySelector( '[data-field="' + fieldName + '"]' );
	}

	function getCategory( bmi ) {
		if ( bmi < 18.5 ) {
			return {
				label: i18n.underweight || 'Underweight',
				range: 'Below 18.5',
				feedback: 'Your BMI is below the standard adult reference range.',
				tone: 'warning',
			};
		}

		if ( bmi < 25 ) {
			return {
				label: i18n.normalWeight || 'Normal Weight',
				range: '18.5 - 24.9',
				feedback: 'Your BMI is within the standard adult reference range.',
				tone: 'success',
			};
		}

		if ( bmi < 30 ) {
			return {
				label: i18n.overweight || 'Overweight',
				range: '25 - 29.9',
				feedback: 'Your BMI is above the standard adult reference range.',
				tone: 'warning',
			};
		}

		return {
			label: i18n.obesity || 'Obesity',
			range: '30+',
			feedback: 'Your BMI is in a high adult reference range.',
			tone: 'danger',
		};
	}

	function showError( calculator, message ) {
		var errorNode = calculator.querySelector( '.sbc-error' );

		if ( ! errorNode ) {
			return;
		}

		errorNode.textContent = message;
		errorNode.hidden = ! message;
	}

	function clearResult( calculator ) {
		var resultBox = calculator.querySelector( '.sbc-result-box' );
		var bmiValue = calculator.querySelector( '.sbc-bmi-value' );
		var categoryBadge = calculator.querySelector( '.sbc-category-badge' );
		var rangeText = calculator.querySelector( '.sbc-range-text' );
		var feedbackText = calculator.querySelector( '.sbc-feedback-text' );
		var underResultCredit = calculator.querySelector( '.sbc-credit--under-result' );

		if ( resultBox ) {
			resultBox.hidden = true;
			resultBox.classList.remove( 'sbc-result-box--success', 'sbc-result-box--warning', 'sbc-result-box--danger' );
		}

		if ( bmiValue ) {
			bmiValue.textContent = '0.0';
			bmiValue.classList.remove( 'sbc-bmi-value--success', 'sbc-bmi-value--warning', 'sbc-bmi-value--danger' );
		}

		if ( categoryBadge ) {
			categoryBadge.textContent = i18n.normalWeight || 'Normal Weight';
			categoryBadge.classList.remove( 'sbc-category-badge--success', 'sbc-category-badge--warning', 'sbc-category-badge--danger' );
		}

		if ( rangeText ) {
			rangeText.textContent = ( i18n.rangePrefix || 'Reference range:' ) + ' 18.5 - 24.9';
		}

		if ( feedbackText ) {
			feedbackText.textContent = 'Your BMI is within the standard adult reference range.';
		}

		if ( underResultCredit ) {
			underResultCredit.hidden = true;
		}
	}

	function updateResult( calculator, result ) {
		var resultBox = calculator.querySelector( '.sbc-result-box' );
		var bmiValue = calculator.querySelector( '.sbc-bmi-value' );
		var categoryBadge = calculator.querySelector( '.sbc-category-badge' );
		var rangeText = calculator.querySelector( '.sbc-range-text' );
		var feedbackText = calculator.querySelector( '.sbc-feedback-text' );
		var underResultCredit = calculator.querySelector( '.sbc-credit--under-result' );
		var category = getCategory( result.bmi );
		var toneClass = category.tone;

		showError( calculator, '' );

		if ( resultBox ) {
			resultBox.hidden = false;
			resultBox.classList.remove( 'sbc-result-box--success', 'sbc-result-box--warning', 'sbc-result-box--danger' );
			resultBox.classList.add( 'sbc-result-box--' + toneClass );
		}

		if ( bmiValue ) {
			bmiValue.textContent = result.bmi.toFixed( 1 );
			bmiValue.classList.remove( 'sbc-bmi-value--success', 'sbc-bmi-value--warning', 'sbc-bmi-value--danger' );
			bmiValue.classList.add( 'sbc-bmi-value--' + toneClass );
		}

		if ( categoryBadge ) {
			categoryBadge.textContent = category.label;
			categoryBadge.classList.remove( 'sbc-category-badge--success', 'sbc-category-badge--warning', 'sbc-category-badge--danger' );
			categoryBadge.classList.add( 'sbc-category-badge--' + toneClass );
		}

		if ( rangeText ) {
			rangeText.textContent = ( i18n.rangePrefix || 'Reference range:' ) + ' ' + category.range;
		}

		if ( feedbackText ) {
			feedbackText.textContent = category.feedback;
		}

		if ( underResultCredit ) {
			underResultCredit.hidden = false;
		}
	}

	function calculateBMI( calculator ) {
		var unit = getCurrentUnit( calculator );

		if ( 'imperial' === unit ) {
			var feet = parseNumber( getField( calculator, 'height-ft' ).value );
			var inches = parseNumber( getField( calculator, 'height-in' ).value );
			var weightLb = parseNumber( getField( calculator, 'weight-lb' ).value );
			var hasFeet = null !== feet;
			var hasInches = null !== inches;
			var hasWeightLb = null !== weightLb;

			if ( false === feet || false === inches || false === weightLb ) {
				return { status: 'invalid' };
			}

			if ( ! hasFeet && ! hasInches && ! hasWeightLb ) {
				return { status: 'empty' };
			}

			if ( ! hasWeightLb || ( ! hasFeet && ! hasInches ) ) {
				return { status: 'incomplete' };
			}

			if ( feet < 0 || inches < 0 || weightLb <= 0 ) {
				return { status: 'invalid' };
			}

			var heightInches = ( feet * 12 ) + inches;

			if ( heightInches <= 0 ) {
				return { status: 'invalid' };
			}

			return {
				status: 'valid',
				bmi: ( 703 * weightLb ) / ( heightInches * heightInches ),
			};
		}

		var heightCm = parseNumber( getField( calculator, 'height-cm' ).value );
		var weightKg = parseNumber( getField( calculator, 'weight-kg' ).value );
		var hasHeightCm = null !== heightCm;
		var hasWeightKg = null !== weightKg;

		if ( false === heightCm || false === weightKg ) {
			return { status: 'invalid' };
		}

		if ( ! hasHeightCm && ! hasWeightKg ) {
			return { status: 'empty' };
		}

		if ( ! hasHeightCm || ! hasWeightKg ) {
			return { status: 'incomplete' };
		}

		if ( heightCm <= 0 || weightKg <= 0 ) {
			return { status: 'invalid' };
		}

		return {
			status: 'valid',
			bmi: weightKg / ( ( heightCm / 100 ) * ( heightCm / 100 ) ),
		};
	}

	function syncFieldGroups( calculator, unit ) {
		calculator.querySelectorAll( '[data-unit-fields]' ).forEach( function ( group ) {
			var isActive = group.getAttribute( 'data-unit-fields' ) === unit;

			group.hidden = ! isActive;

			group.querySelectorAll( '.sbc-input' ).forEach( function ( input ) {
				input.disabled = ! isActive;
			} );
		} );
	}

	function setUnit( calculator, unit ) {
		var toggles = calculator.querySelectorAll( '.sbc-unit-toggle__button' );

		calculator.setAttribute( 'data-current-unit', unit );
		calculator.classList.toggle( 'sbc-unit-metric', 'metric' === unit );
		calculator.classList.toggle( 'sbc-unit-imperial', 'imperial' === unit );

		toggles.forEach( function ( toggle ) {
			var isActive = toggle.getAttribute( 'data-unit' ) === unit;

			toggle.classList.toggle( 'sbc-unit-toggle__button--active', isActive );
			toggle.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );
			toggle.tabIndex = isActive ? 0 : -1;
		} );

		syncFieldGroups( calculator, unit );
		showError( calculator, '' );
		clearResult( calculator );
	}

	function processCalculation( calculator, showIncompleteError ) {
		var result = calculateBMI( calculator );
		var unit = getCurrentUnit( calculator );
		var errorMessage = 'imperial' === unit
			? ( i18n.errorImperial || 'Enter valid height in feet and inches, and weight in pounds.' )
			: ( i18n.errorMetric || 'Enter valid height in centimeters and weight in kilograms.' );

		if ( 'valid' === result.status ) {
			updateResult( calculator, result );
			return;
		}

		clearResult( calculator );

		if ( 'invalid' === result.status || ( showIncompleteError && 'incomplete' === result.status ) ) {
			showError( calculator, errorMessage );
			return;
		}

		showError( calculator, '' );
	}

	function bindToggleKeyboard( toggles ) {
		toggles.forEach( function ( toggle ) {
			toggle.addEventListener( 'keydown', function ( event ) {
				if ( 'ArrowLeft' !== event.key && 'ArrowRight' !== event.key ) {
					return;
				}

				event.preventDefault();

				var toggleList = Array.prototype.slice.call( toggles );
				var currentIndex = toggleList.indexOf( toggle );
				var nextIndex = 'ArrowRight' === event.key ? currentIndex + 1 : currentIndex - 1;

				if ( nextIndex < 0 ) {
					nextIndex = toggleList.length - 1;
				}

				if ( nextIndex >= toggleList.length ) {
					nextIndex = 0;
				}

				toggleList[ nextIndex ].focus();
				toggleList[ nextIndex ].click();
			} );
		} );
	}

	function initCalculator( calculator ) {
		var toggles = calculator.querySelectorAll( '.sbc-unit-toggle__button' );
		var inputs = calculator.querySelectorAll( '.sbc-input' );
		var button = calculator.querySelector( '.sbc-calc-btn' );
		var defaultUnit = getCurrentUnit( calculator );

		setUnit( calculator, defaultUnit );
		bindToggleKeyboard( toggles );

		toggles.forEach( function ( toggle ) {
			toggle.addEventListener( 'click', function () {
				setUnit( calculator, toggle.getAttribute( 'data-unit' ) );
			} );
		} );

		inputs.forEach( function ( input ) {
			input.addEventListener( 'input', function () {
				processCalculation( calculator, false );
			} );
		} );

		if ( button ) {
			button.addEventListener( 'click', function () {
				processCalculation( calculator, true );
			} );
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '.sbc-calculator' ).forEach( function ( calculator ) {
			initCalculator( calculator );
		} );
	} );
}() );
