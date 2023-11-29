<template>
    <section class="box__buyers__page">
        <!--   eslint-disable   -->
        <div class="container">
            <div class="row">
                <div class="col-5">
                    <h2>Формирование ценников</h2>
                </div>
                <div class="col-7">
                    <div class="wraapper__button-nowrap content__justify-end">
                        <div class="btn btn__icons"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-dollar.svg') })` }"></span>Шаблон ценника</button></div>
                        <div class="btn btn__icons"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-eye-white.svg') })` }"></span>Предпросмотр</button></div>
                        <div class="btn btn__icons"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-printer.svg') })` }"></span>Печать</button></div>
                        <div class="btn btn__icons"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-email.svg') })` }"></span>Отправить на почту</button></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 margin-30">
                    <div class="wraapper__button-nowrap content__justify-end">
                        <div class="btn btn__icons"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-import.svg') })` }"></span>Импорт из номенкалатуры</button></div>
                        <div class="btn btn__icons"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-import.svg') })` }"></span>Импорт из файла</button></div>
                        <div class="btn btn__icon-purple"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-add.svg') })` }"></span></button></div>
                        <div class="btn btn__icon-purple"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-copy.svg') })` }"></span></button></div>
                        <div class="btn btn__icon-white border-purple"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-trash-purple.svg') })` }"></span></button></div>
                        <div class="btn-whitepurple btn__icon-white margin-left-20"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-arrow-up.svg') })` }"></span></button></div>
                        <div class="btn-whitepurple btn__icon-white"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-arrow-down.svg') })` }"></span></button></div>
                        <div class="box__search box__search-price">
                            <form>
                                <div class="box__input"><input type="text" placeholder="Поиск"></div>
                                <div class="btn__search"><button></button></div>
                            </form>
                        </div>
                        <div class="btn btn__icons"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-columns.svg') })` }"></span>Добавить колонки</button></div>
                        <div class="btn btn__icons"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-filter.svg') })` }"></span>Фильтровать</button></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="box__content__table">
                        <div class="table__content-title">
                            <div class="row">
                                <div class="box__col-num">№</div>
                                <div class="col-3">
                                    <h3>Номенклатура</h3>
                                </div>
                                <div class="col-1">
                                    <h3>Ед. изм</h3>
                                </div>
                                <div class="col-1">
                                    <h3>Код товара</h3>
                                </div>
                                <div class="col-1">
                                    <h3>Штрихкод</h3>
                                </div>
                                <div class="col-2">
                                    <h3>Старая цена</h3>
                                </div>
                                <div class="col-1">
                                    <h3>Цена</h3>
                                </div>
                                <div class="col-2">
                                    <h3>Кол-во ценников</h3>
                                </div>
                                <div class="col-1">
                                    <h3>Действие</h3>
                                </div>
                            </div>
                        </div>
                        <div class="table__content">
                            <div class="box__table__item box__table-export">
                                <div class="box__item" data-table>
                                    <div class="row">
                                        <div class="box__col-num">1</div>
                                        <div class="col-3">
                                            <div class="box__checkbox">
                                                <div class="wrapper-checkbox">
                                                    <label>
                                                        <input type="checkbox" checked="">
                                                        <span>
                                                                            <span class="box__checkbox-icon"></span>
                                                                    </span></label>
                                                </div>
                                            </div>
                                            Сыр полутвердый Ламбер 50%
                                        </div>
                                        <div class="col-1">Кг</div>
                                        <div class="col-1">cPRzm8ip</div>
                                        <div class="col-1">A-0030-Z</div>
                                        <div class="col-2">2000</div>
                                        <div class="col-1">1200</div>
                                        <div class="col-2">
                                            <div class="box__quality">
                                                <div class="box__quality-value"><input type="number" data-number="0" step="1" min="1" max="100" value="1"></div>
                                                <span class="btn__quality-nav">
                                                                    <span class="btn__quality-minus" data-prev-quality>-</span>
                                                                    <span class="btn__quality-plus" data-next-quality>+</span>
                                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-1">
                                            <div class="wraapper__button-nowrap">
                                                <div class="btn btn__icon-purple"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-edit.svg') })` }"></span></button></div>
                                                <div class="btn-whitepurple btn__icon-whitepurple"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-trash.svg') })` }"></span></button></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="box__table__item">
                                <div class="box__item box__item-edit" data-table>
                                    <div class="row">
                                        <div class="box__col-num">2</div>
                                        <div class="col-3">
                                            <div class="box__input">
                                                <input type="text" placeholder="Номенклатура" value="Сыр полутвердый Ламбер 50%">
                                            </div>
                                        </div>
                                        <div class="col-1">
                                            <div class="box__select">
                                                <select>
                                                    <option value="1-2">Кг</option>
                                                    <option value="1-3">Гр</option>
                                                    <option value="2-1">Мг</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-1">
                                            <div class="box__input">
                                                <input type="text" placeholder="Артикул">
                                            </div>
                                        </div>
                                        <div class="col-1">
                                            <div class="box__input">
                                                <span class="input__icon" :style="{ backgroundImage: `url(${ require('@/assets/img/icon/barcode.svg') })` }"></span>
                                                <input type="text" placeholder="Шрихкод">
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="box__input">
                                                <input type="text" placeholder="Старая цена">
                                            </div>
                                        </div>
                                        <div class="col-1">
                                            <div class="box__input">
                                                <input type="text" placeholder="Цена">
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="box__quality">
                                                <div class="box__quality-value"><input type="number" data-number="0" step="1" min="1" max="100" value="1"></div>
                                                <span class="btn__quality-nav">
                                                                    <span class="btn__quality-minus" data-prev-quality>-</span>
                                                                    <span class="btn__quality-plus" data-next-quality>+</span>
                                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-1">
                                            <div class="wraapper__button-nowrap">
                                                <div class="btn btn__icon-purple"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/table-accept.svg') })` }"></span></button></div>
                                                <div class="btn-whitepurple btn__icon-whitepurple"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-trash.svg') })` }"></span></button></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="box__table__item box__table-export">
                                <div class="box__item" data-table>
                                    <div class="row">
                                        <div class="box__col-num">3</div>
                                        <div class="col-3">
                                            <div class="box__checkbox">
                                                <div class="wrapper-checkbox">
                                                    <label>
                                                        <input type="checkbox" checked="">
                                                        <span>
                                                                            <span class="box__checkbox-icon"></span>
                                                                    </span></label>
                                                </div>
                                            </div>
                                            Сыр полутвердый Ламбер 50%
                                        </div>
                                        <div class="col-1">Кг</div>
                                        <div class="col-1">cPRzm8ip</div>
                                        <div class="col-1">A-0030-Z</div>
                                        <div class="col-2">2000</div>
                                        <div class="col-1">1200</div>
                                        <div class="col-2">
                                            <div class="box__quality">
                                                <div class="box__quality-value"><input type="number" data-number="0" step="1" min="1" max="100" value="1"></div>
                                                <span class="btn__quality-nav">
                                                                    <span class="btn__quality-minus" data-prev-quality>-</span>
                                                                    <span class="btn__quality-plus" data-next-quality>+</span>
                                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-1">
                                            <div class="wraapper__button-nowrap">
                                                <div class="btn btn__icon-purple"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-edit.svg') })` }"></span></button></div>
                                                <div class="btn-whitepurple btn__icon-whitepurple"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-trash.svg') })` }"></span></button></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="box__table__item box__table-export">
                                <div class="box__item" data-table>
                                    <div class="row">
                                        <div class="box__col-num">4</div>
                                        <div class="col-3">
                                            <div class="box__checkbox">
                                                <div class="wrapper-checkbox">
                                                    <label>
                                                        <input type="checkbox">
                                                        <span>
                                                                            <span class="box__checkbox-icon"></span>
                                                                    </span></label>
                                                </div>
                                            </div>
                                            Сыр полутвердый Ламбер 50%
                                        </div>
                                        <div class="col-1">Кг</div>
                                        <div class="col-1">cPRzm8ip</div>
                                        <div class="col-1">A-0030-Z</div>
                                        <div class="col-2">2000</div>
                                        <div class="col-1">1200</div>
                                        <div class="col-2">
                                            <div class="box__quality">
                                                <div class="box__quality-value"><input type="number" data-number="0" step="1" min="1" max="100" value="1"></div>
                                                <span class="btn__quality-nav">
                                                                    <span class="btn__quality-minus" data-prev-quality>-</span>
                                                                    <span class="btn__quality-plus" data-next-quality>+</span>
                                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-1">
                                            <div class="wraapper__button-nowrap">
                                                <div class="btn btn__icon-purple"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-edit.svg') })` }"></span></button></div>
                                                <div class="btn-whitepurple btn__icon-whitepurple"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-trash.svg') })` }"></span></button></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="box__table__item box__table-export">
                                <div class="box__item" data-table>
                                    <div class="row">
                                        <div class="box__col-num">5</div>
                                        <div class="col-3">
                                            <div class="box__checkbox">
                                                <div class="wrapper-checkbox">
                                                    <label>
                                                        <input type="checkbox">
                                                        <span>
                                                                            <span class="box__checkbox-icon"></span>
                                                                    </span></label>
                                                </div>
                                            </div>
                                            Сыр полутвердый Ламбер 50%
                                        </div>
                                        <div class="col-1">Кг</div>
                                        <div class="col-1">cPRzm8ip</div>
                                        <div class="col-1">A-0030-Z</div>
                                        <div class="col-2">2000</div>
                                        <div class="col-1">1200</div>
                                        <div class="col-2">
                                            <div class="box__quality">
                                                <div class="box__quality-value"><input type="number" data-number="0" step="1" min="1" max="100" value="1"></div>
                                                <span class="btn__quality-nav">
                                                                    <span class="btn__quality-minus" data-prev-quality>-</span>
                                                                    <span class="btn__quality-plus" data-next-quality>+</span>
                                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-1">
                                            <div class="wraapper__button-nowrap">
                                                <div class="btn btn__icon-purple"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-edit.svg') })` }"></span></button></div>
                                                <div class="btn-whitepurple btn__icon-whitepurple"><button><span :style="{ backgroundImage: `url(${ require('@/assets/img/icon/btn-trash.svg') })` }"></span></button></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table__content-bottom">
                            <div class="row">
                                <div class="col-12">
                                    <div class="box__pagination">
                                        <ul>
                                            <li class="prev"><a href="#"><span></span></a></li>
                                            <li><a href="#">1</a></li>
                                            <li><a href="#">2</a></li>
                                            <li class="active"><a href="#">3</a></li>
                                            <li><a href="#">4</a></li>
                                            <li><a href="#">...</a></li>
                                            <li><a href="#">7</a></li>
                                            <li class="next"><a href="#"><span></span></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
/* eslint-disable */
export default {
    name: 'index'
};
</script>

<style scoped>

</style>
