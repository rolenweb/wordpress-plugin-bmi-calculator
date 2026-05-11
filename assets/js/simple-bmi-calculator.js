( function () {
	'use strict';

	var config = window.sbcCalculatorData || {};
	var i18n = config.i18n || {};

	function parseNumber( value ) {
		var parsed = parseFloat( value );

		return Number.isFinite( parsed ) ? parsed : null;
	}

	function getCurrentUnit( calculator ) {
		return calculator.getAttribute( 'data-current-unit' ) || calculator.getAttribute( 'data-default-unit' ) || 'metric';
	}

	function getCategoryData( bmi ) {
		if ( bmi < 18.5 ) {
			return {
				label: i18n.underweight || 'Underweight',
				numberClass: 'sbc-result__number--underweight',
				badgeClass: 'sbc-result__badge--underweight',
			};
		}

		if ( bmi < 25 ) {
			return {
				label: i18n.healthy || 'Healthy',
				numberClass: 'sbc-result__number--healthy',
				badgeClass: 'sbc-result__badge--healthy',
			};
		}

		if ( bmi < 30 ) {
			return {
				label: i18n.overweight || 'Overweight',
				numberClass: 'sbc-result__number--overweight',
				badgeClass: 'sbc-result__badge--overweight',
			};
		}

		return {
			label: i18n.obese || 'Obese',
			numberClass: 'sbc-result__number--obese',
			badgeClass: 'sbc-result__badge--obese',
		};
	}

	function setError( calculator, message ) {
		var errorNode = calculator.querySelector( '.sbc-error' );

		if ( ! errorNode ) {
			return;
		}

		errorNode.textContent = message;
		errorNode.hidden = ! message;
	}

	function resetResult( calculator ) {
		var result = calculator.querySelector( '.sbc-result' );
		var resultNumber = calculator.querySelector( '.sbc-result__number' );
		var resultBadge = calculator.querySelector( '.sbc-result__badge' );

		if ( result ) {
			result.hidden = true;
		}

		if ( resultNumber ) {
			resultNumber.textContent = '0.0';
			resultNumber.className = 'sbc-result__number';
		}

		if ( resultBadge ) {
			resultBadge.textContent = i18n.healthy || 'Healthy';
			resultBadge.className = 'sbc-result__badge';
		}
	}

	function updateLabels( calculator, unit ) {
		calculator.querySelectorAll( '.sbc-field__label' ).forEach( function ( label ) {
			var metricLabel = label.getAttribute( 'data-label-metric' );
			var imperialLabel = label.getAttribute( 'data-label-imperial' );

			label.textContent = unit === 'imperial' ? imperialLabel : metricLabel;
		} );
	}

	function setActiveUnit( calculator, unit ) {
		var toggles = calculator.querySelectorAll( '.sbc-unit-toggle__button' );

		calculator.setAttribute( 'data-current-unit', unit );
		updateLabels( calculator, unit );

		toggles.forEach( function ( toggle ) {
			var isActive = toggle.getAttribute( 'data-unit' ) === unit;

			toggle.classList.toggle( 'sbc-unit-toggle__button--active', isActive );
			toggle.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );
			toggle.tabIndex = isActive ? 0 : -1;
		} );
	}

	function calculateBmi( calculator ) {
		var unit = getCurrentUnit( calculator );
		var heightInput = calculator.querySelector( '[data-field="height"]' );
		var weightInput = calculator.querySelector( '[data-field="weight"]' );
		var height = parseNumber( heightInput ? heightInput.value : '' );
		var weight = parseNumber( weightInput ? weightInput.value : '' );

		if ( ! height && '' !== ( heightInput ? heightInput.value.trim() : '' ) ) {
			return 'invalid';
		}

		if ( ! weight && '' !== ( weightInput ? weightInput.value.trim() : '' ) ) {
			return 'invalid';
		}

		if ( null === height || null === weight || '' === heightInput.value.trim() || '' === weightInput.value.trim() ) {
			return null;
		}

		if ( height <= 0 || weight <= 0 ) {
			return 'invalid';
		}

		if ( 'imperial' === unit ) {
			return ( weight * 703 ) / ( height * height );
		}

		return weight / Math.pow( height / 100, 2 );
	}

	function renderResult( calculator ) {
		var result = calculator.querySelector( '.sbc-result' );
		var resultNumber = calculator.querySelector( '.sbc-result__number' );
		var resultBadge = calculator.querySelector( '.sbc-result__badge' );
		var bmi = calculateBmi( calculator );

		if ( 'invalid' === bmi ) {
			setError( calculator, i18n.errorMessage || 'Please enter valid height and weight values.' );
			resetResult( calculator );
			return;
		}

		if ( null === bmi ) {
			setError( calculator, '' );
			resetResult( calculator );
			return;
		}

		var category = getCategoryData( bmi );

		setError( calculator, '' );

		if ( result ) {
			result.hidden = false;
		}

		if ( resultNumber ) {
			resultNumber.textContent = bmi.toFixed( 1 );
			resultNumber.className = 'sbc-result__number ' + category.numberClass;
		}

		if ( resultBadge ) {
			resultBadge.textContent = category.label;
			resultBadge.className = 'sbc-result__badge ' + category.badgeClass;
		}
	}

	function bindCalculator( calculator ) {
		var toggles = calculator.querySelectorAll( '.sbc-unit-toggle__button' );
		var inputs = calculator.querySelectorAll( '.sbc-input' );
		var defaultUnit = getCurrentUnit( calculator );

		setActiveUnit( calculator, defaultUnit );
		resetResult( calculator );

		toggles.forEach( function ( toggle ) {
			toggle.addEventListener( 'click', function () {
				setActiveUnit( calculator, toggle.getAttribute( 'data-unit' ) );
				renderResult( calculator );
			} );

			toggle.addEventListener( 'keydown', function ( event ) {
				if ( event.key !== 'ArrowLeft' && event.key !== 'ArrowRight' ) {
					return;
				}

				event.preventDefault();

				var direction = event.key === 'ArrowRight' ? 1 : -1;
				var toggleList = Array.prototype.slice.call( toggles );
				var currentIndex = toggleList.indexOf( toggle );
				var nextIndex = ( currentIndex + direction + toggleList.length ) % toggleList.length;
				var nextToggle = toggleList[ nextIndex ];

				if ( nextToggle ) {
					nextToggle.focus();
					nextToggle.click();
				}
			} );
		} );

		inputs.forEach( function ( input ) {
			input.addEventListener( 'input', function () {
				renderResult( calculator );
			} );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '.sbc-calculator' ).forEach( bindCalculator );
	} );
}() );
