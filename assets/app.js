/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.scss';

document.addEventListener('DOMContentLoaded', () => {
    const skillModal = document.getElementById('skill-modal');

    if (!skillModal) {
        return;
    }

    const skillCards = document.querySelectorAll('.portfolio-home button.skill-card');
    const closeButton = skillModal.querySelector('.skill-modal__close');
    const backdrop = skillModal.querySelector('.skill-modal__backdrop');
    const title = skillModal.querySelector('#skill-modal-title');
    const description = skillModal.querySelector('#skill-modal-description');
    const rating = skillModal.querySelector('.skill-modal__rating-value');

    let activeSkill = null;

    const openSkillModal = (skillCard) => {
        const skillName = skillCard.dataset.skillName || '';
        const descriptionData = skillCard.querySelector(
            '.skill-card__description-data'
        );

        const skillDescription = descriptionData
            ? descriptionData.textContent.trim()
            : '';
        const skillRating = skillCard.dataset.skillRating || '0';

        title.textContent = skillName;
        description.textContent = skillDescription;
        rating.textContent = skillRating;

        activeSkill = skillCard;

        skillModal.classList.add('is-open');
        skillModal.setAttribute('aria-hidden', 'false');

        document.body.style.overflow = 'hidden';

        closeButton.focus();
    };

    const closeSkillModal = () => {
        skillModal.classList.remove('is-open');
        skillModal.setAttribute('aria-hidden', 'true');

        document.body.style.overflow = '';

        if (activeSkill) {
            activeSkill.focus();
            activeSkill = null;
        }
    };

    skillCards.forEach((skillCard) => {
        skillCard.addEventListener('click', () => {
            openSkillModal(skillCard);
        });
    });

    closeButton.addEventListener('click', closeSkillModal);

    backdrop.addEventListener('click', closeSkillModal);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && skillModal.classList.contains('is-open')) {
            closeSkillModal();
        }
    });
});