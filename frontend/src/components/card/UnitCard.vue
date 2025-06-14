<template>
  <div 
    class="unit-card"
    :class="{ 
      'blurred': isBlurred,
      'certified': unit?.isLearned,
      'non-certified': !unit?.isLearned
    }"
    @click="handleUnitClick"
  >    <!-- Certified Card Layout (Same Structure as Non-Certified) -->
    <div v-if="unit && unit.isLearned" class="unit-content">
      <!-- Card Header -->
      <div class="unit-header">
        <div class="unit-title">{{ unit.title }}</div>
      </div>

      <!-- Card Body -->
      <div class="unit-body">
        <div class="unit-points">
          <span class="unit-points-number">{{ unit.points }}</span>
          <span class="unit-points-label">pts left</span>
        </div>
        
        <!-- Progress indicator for certified cards -->
        <div class="unit-progress">
          <div class="progress-bar">
            <div class="progress-fill" :style="{ width: getProgressPercentage() }"></div>
          </div>
          <span class="progress-text">{{ getProgressText() }}</span>        </div>
      </div>

      <!-- Card Footer -->
      <div class="unit-footer">
        <div class="unit-badge">
          <Icon 
            :name="'specialist-full'"
            size="sm"
            :alt="'Unit completed'"
            class="badge-icon"
          />
        </div>
      </div>
    </div>

    <!-- Non-Certified Card Layout (Existing Structure) -->
    <div v-else-if="unit && !unit.isLearned" class="unit-content">
      <!-- Card Header -->
      <div class="unit-header">
        <div class="unit-title">{{ unit.title }}</div>
      </div>

      

      <!-- Card Footer -->
      <div class="unit-footer">
        <div class="unit-badge">
          <Icon 
            :name="'specialist-empty'"
            size="sm"
            :alt="'Unit not completed'"
            class="badge-icon"
          />
        </div>
      </div>
    </div>
    
    <!-- Debug info when unit is undefined -->
    <div v-else class="unit-error">
      <p>Error: Unit prop is undefined</p>
    </div>    <!-- Overlay when unit is clicked -->
    <div v-if="isClicked && unit" class="unit-overlay">      <!-- Certified Card Overlay (existing structure) -->
      <div v-if="unit.isLearned" class="overlay-content">
        <div class="overlay-buttons">
          <OutlineButton 
            label="Learn"
            class="learn-button-component"
            @click="(event) => { event.stopPropagation(); handleLearnClick(); }"
          />
          <OutlineButton 
            label="Quiz"
            class="quiz-button-component"
            :disabled="!unit.isLearned"
            @click="(event) => { event.stopPropagation(); handleQuizClick(); }"
          />
        </div>
      </div>      <!-- Non-Certified Card Overlay (same structure as certified) -->
      <div v-else class="overlay-content">
        <div class="overlay-buttons">
          <OutlineButton 
            label="Learn"
            class="learn-button-component"
            @click="(event) => { event.stopPropagation(); handleLearnClick(); }"
          />
          <OutlineButton 
            label="Quiz"
            class="quiz-button-component"
            :disabled="true"
            @click="(event) => { event.stopPropagation(); }"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { OutlineButton } from '../button'
import Icon from '../Icon.vue'

export default {
  name: 'UnitCard',
  components: {
    OutlineButton,
    Icon
  },props: {
    unit: {
      type: Object,
      required: false, // Temporarily changed from true to false for debugging
      default: null,
      validator(value) {
        if (!value) {
          console.warn('UnitCard: unit prop is null/undefined');
          return true; // Allow null/undefined for debugging
        }
        const isValid = value && 
          typeof value.id !== 'undefined' &&
          typeof value.title === 'string' &&
          typeof value.points === 'string' &&
          typeof value.badgeText === 'string' &&
          typeof value.isLearned === 'boolean';
        
        if (!isValid) {
          console.error('UnitCard: Invalid unit prop structure:', value);
        }
        return isValid;
      }
    },
    isClicked: {
      type: Boolean,
      default: false
    },
    isBlurred: {
      type: Boolean,
      default: false
    }
  },
  emits: ['unit-click', 'learn-unit', 'quiz-unit'],  methods: {
    handleUnitClick() {
      if (this.unit) {
        this.$emit('unit-click', this.unit);
      } else {
        console.error('UnitCard: Cannot handle unit click - unit is undefined');
      }
    },
    handleLearnClick() {
      if (this.unit) {
        this.$emit('learn-unit', this.unit);
      } else {
        console.error('UnitCard: Cannot handle learn click - unit is undefined');
      }
    },
    handleQuizClick() {
      if (this.unit && this.unit.isLearned) {
        this.$emit('quiz-unit', this.unit);
        // Navigate to quiz start page
        this.$router.push({
          name: 'QuizStart',
          params: {
            unitId: this.unit.id,
            chapterId: this.unit.chapterId || 'default'
          }
        });
      } else if (!this.unit) {
        console.error('UnitCard: Cannot handle quiz click - unit is undefined');
      }
    },
    getProgressPercentage() {
      if (!this.unit || this.unit.isLearned) return '0%';
      // Calculate progress based on points left (lower points = more progress)
      const totalPoints = parseInt(this.unit.totalPoints) || 100;
      const pointsLeft = parseInt(this.unit.points) || 0;
      const progress = Math.max(0, Math.min(100, ((totalPoints - pointsLeft) / totalPoints) * 100));
      return `${progress}%`;
    },
    getProgressText() {
      if (!this.unit || this.unit.isLearned) return '';
      const progress = parseInt(this.getProgressPercentage());
      if (progress === 0) return 'Not started';
      if (progress < 50) return 'In progress';
      if (progress < 100) return 'Almost done';
      return 'Complete';
    }
  }
}
</script>

<style scoped>
/* Unit Card Base */
.unit-card {
  height: 208px;
  min-width: 160px;
  width: 160px;
  flex-shrink: 0;
  position: relative;
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 8px;
}

/* Certified cards use the same styling as non-certified */
.unit-card.certified {
  padding: 16px;
  background: var(--card-background-color-light, #FFF);
  box-shadow: 0px 0px 8px 0px rgba(193, 200, 210, 1.00);
  border: 1px solid var(--card-text-color-dark, #072C54);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: stretch;
  overflow: hidden;
}

/* Non-certified cards use the existing Vue/CSS structure */
.unit-card.non-certified {
  padding: 16px;
  background: var(--card-background-color-light, #FFF);
  box-shadow: 0px 0px 8px 0px rgba(193, 200, 210, 1.00);
  border: 1px solid var(--card-text-color-dark, #072C54);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: stretch;
  overflow: hidden;
}

.unit-card:hover {
  transform: translateY(-2px);
  box-shadow: 0px 4px 16px 0px rgba(193, 200, 210, 0.6);
}

/* Blur effect for non-clicked units */
.unit-card.blurred {
  filter: blur(2px);
}

.unit-card.blurred::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.3);
  z-index: 1;
  pointer-events: none;
  border-radius: 8px;
}

/* Card Content Structure (Non-certified cards) */
.unit-content {
  display: flex;
  flex-direction: column;
  gap: 12px;
  width: 100%;
  height: 100%;
  justify-content: space-between;
}

/* Card Header (Non-certified cards) */
.unit-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  width: 100%;
}

.unit-title {
  flex: 1;
  color: var(--card-text-color-dark, #072C54);
  font-family: "Italian Plate No2";
  font-size: 18px;
  font-style: normal;
  font-weight: 600;
  line-height: 1.2;
  text-transform: uppercase;
  text-align: left;
  margin-right: 8px;
}

.unit-status-badge {
  background: var(--color-secondary-grey-20, #F3F4F6);
  border-radius: 50%;
  padding: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.status-icon {
  width: 16px;
  height: 16px;
}

/* Card Body (Non-certified cards) */
.unit-body {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.unit-points {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.unit-points-number {
  color: var(--color-primary-yellow-100, #FFC72C);
  font-family: "Italian Plate No2";
  font-size: 18px;
  font-style: normal;
  font-weight: 600;
  line-height: normal;
  text-transform: uppercase;
}

.unit-points-label {
  color: var(--color-primary-blue-100, #072C54);
  font-family: "Italian Plate No2";
  font-size: 16px;
  font-style: normal;
  font-weight: 400;
  line-height: normal;
}

/* Progress Section (Non-certified cards only) */
.unit-progress {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-top: 8px;
}

.progress-bar {
  width: 100%;
  height: 4px;
  background: var(--color-secondary-grey-20, #F3F4F6);
  border-radius: 2px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--color-primary-blue-100, #072C54) 0%, var(--color-primary-blue-80, #1E40AF) 100%);
  border-radius: 2px;
  transition: width 0.3s ease;
}

.progress-text {
  color: var(--color-secondary-grey-80, #6B7280);
  font-family: "Italian Plate No2";
  font-size: 11px;
  font-weight: 400;
  text-transform: uppercase;
}

/* Card Footer (Non-certified cards) */
.unit-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  width: 100%;
}

.unit-badge {
  padding: 4px 8px;
  border-radius: 4px;
  display: inline-flex;
  justify-content: center;
  align-items: center;
}

.badge-icon {
  width: 16px;
  height: 16px;
}

/* Error display styling */
.unit-error {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  height: 100%;
  color: #ff0000;
  background: #ffeeee;
  border: 1px dashed #ff0000;
  padding: 8px;
  text-align: center;
  border-radius: 4px;
}

.unit-error p {
  margin: 0;
  font-size: 12px;
  font-weight: bold;
}

/* Unit Overlay */
.unit-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 10;
  border-radius: 8px;
}

.overlay-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 8px 16px 16px 16px; /* Reduced top padding from 16px to 8px */
}

.overlay-title {
  color: var(--color-white, #FFFFFF);
  font-family: "Italian Plate No2";
  font-size: 16px;
  font-weight: 600;
  text-transform: uppercase;
  text-align: center;
  margin-bottom: 8px;
}

.overlay-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* Custom styles for button components in overlay */
.learn-button-component,
.quiz-button-component {
  width: 80px !important;
  height: 48px !important;
  min-width: 60px !important;
  max-width: 80px !important;
  padding: 8px 12px !important;
  font-size: 12px !important;
  font-weight: 600 !important;
}

/* Responsive Design */
@media (max-width: 640px) {
  .unit-card {
    height: 180px;
    min-width: 140px;
    width: 140px;
  }
  
  .unit-card.certified,
  .unit-card.non-certified {
    padding: 12px;
    gap: 8px;
  }
  
  .unit-title {
    font-size: 16px;
  }
  
  .unit-points-number {
    font-size: 16px;
  }
  
  .unit-points-label {
    font-size: 14px;
  }
  
  .overlay-title {
    font-size: 14px;
  }
  
  .learn-button-component,
  .quiz-button-component {
    width: 70px !important;
    height: 40px !important;
    font-size: 11px !important;
  }
}

@media (max-width: 480px) {
  .unit-card {
    height: 160px;
    min-width: 120px;
    width: 120px;
  }
  
  .unit-card.certified,
  .unit-card.non-certified {
    padding: 10px;
  }
  
  .unit-title {
    font-size: 14px;
  }
  
  .unit-points-number {
    font-size: 14px;
  }
  
  .unit-points-label {
    font-size: 12px;
  }
}
</style>
