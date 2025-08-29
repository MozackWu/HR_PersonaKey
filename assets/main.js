// 人格測驗系統 - 主JavaScript檔案

// 工具函數
function checkLength(data, length) {
    return (data.length >= length);
}

// 檢查身份證字號
function checkID(id) {
    const tab = "ABCDEFGHJKLMNPQRSTUVXYWZIO";
    const A1 = [1,1,1,1,1,1,1,1,1,1,2,2,2,2,2,2,2,2,2,2,3,3,3,3,3,3];
    const A2 = [0,1,2,3,4,5,6,7,8,9,0,1,2,3,4,5,6,7,8,9,0,1,2,3,4,5];
    const Mx = [9,8,7,6,5,4,3,2,1,1];

    if (id.length !== 10) return false;
    
    const i = tab.indexOf(id.charAt(0));
    if (i === -1) return false;
    
    let sum = A1[i] + A2[i] * 9;

    for (let j = 1; j < 10; j++) {
        const v = parseInt(id.charAt(j));
        if (isNaN(v)) return false;
        sum = sum + v * Mx[j];
    }
    
    return (sum % 10 === 0);
}

// 顯示錯誤訊息
function showError(message) {
    let errorDiv = document.querySelector('.error-message');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        const container = document.querySelector('.login-container') || document.querySelector('.test-container');
        if (container) {
            container.insertBefore(errorDiv, container.querySelector('form'));
        }
    }
    errorDiv.textContent = message;
    
    setTimeout(() => {
        if (errorDiv.parentNode) {
            errorDiv.parentNode.removeChild(errorDiv);
        }
    }, 5000);
}

// 登入頁面功能
function validateAndSubmit() {
    const name = document.getElementById('name')?.value.trim();
    const id = document.getElementById('id')?.value.trim().toUpperCase();
    const token = document.getElementById('token')?.value.trim();
    
    if (name && !checkLength(name, 2)) {
        showError('請輸入正確的姓名（至少2個字符）');
        document.getElementById('name').focus();
        return;
    }
    
    if (id) {
        document.getElementById('id').value = id;
        if (!checkID(id)) {
            showError('請輸入正確的身份證字號');
            document.getElementById('id').focus();
            return;
        }
    }
    
    if (token !== undefined && !token) {
        showError('請輸入登入碼');
        document.getElementById('token').focus();
        return;
    }
    
    const form = document.getElementById('loginForm') || document.getElementById('queryForm');
    if (form) {
        form.submit();
    }
}

// 測驗頁面功能
function updateSelection(questionName) {
    document.querySelectorAll(`input[name="${questionName}"]`).forEach(radio => {
        const option = radio.closest('.answer-option');
        if (radio.checked) {
            option.classList.add('selected');
        } else {
            option.classList.remove('selected');
        }
    });
}

function validateAndNext() {
    const radioGroups = document.querySelectorAll('input[type="radio"]');
    const questionNames = [...new Set(Array.from(radioGroups).map(r => r.name))];
    
    let unAnswered = 0;
    let firstUnanswered = null;
    
    questionNames.forEach((name, index) => {
        const radios = document.querySelectorAll(`input[name="${name}"]`);
        const isAnswered = Array.from(radios).some(r => r.checked);
        if (!isAnswered) {
            unAnswered++;
            if (!firstUnanswered) {
                firstUnanswered = radios[0].closest('.question-container');
            }
        }
    });
    
    if (unAnswered > 0) {
        alert(`還有 ${unAnswered} 道題目尚未回答，請完成後再繼續。`);
        if (firstUnanswered) {
            firstUnanswered.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }
    
    const form = document.getElementById('testForm');
    if (form) {
        form.submit();
    }
}

// 結果頁面功能
function submitForm() {
    const token = document.getElementById('token')?.value.trim();
    if (!token) {
        alert('請輸入列印碼');
        return;
    }
    document.getElementById('queryForm').submit();
}

// DOM載入完成後初始化
document.addEventListener('DOMContentLoaded', function() {
    // Enter鍵提交功能
    document.addEventListener('keyup', function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            if (document.getElementById('loginForm')) {
                validateAndSubmit();
            } else if (document.getElementById('testForm')) {
                validateAndNext();
            } else if (document.getElementById('queryForm')) {
                submitForm();
            }
        }
    });
    
    // 測驗頁面選項點擊功能
    document.querySelectorAll('.answer-option').forEach(option => {
        option.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                updateSelection(radio.name);
            }
        });
    });
    
    // 測驗頁面選項變更功能
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            updateSelection(this.name);
        });
    });
});

// 舊版瀏覽器兼容性
if (!Array.from) {
    Array.from = function(arrayLike) {
        return Array.prototype.slice.call(arrayLike);
    };
}

if (!String.prototype.trim) {
    String.prototype.trim = function() {
        return this.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
    };
}