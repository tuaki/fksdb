import * as React from 'react';
import { Field } from 'redux-form';
import { IPersonSelector } from '../../../../brawl-registration/middleware/price';
import Lang from '../../../../lang/components/lang';
import { required as requiredTest } from '../../../validation';
import InputProvider from '../../input-provider';
import BaseInput, { IBaseInputProps } from '../../inputs/base-input';
import { IInputDefinition } from '../interfaces';

class Input extends InputProvider<IBaseInputProps> {
}

interface IProps {
    def: IInputDefinition;
    personSelector: IPersonSelector;
    name: string;
}

export default class Email extends React.Component<IProps, {}> {
    public render() {
        const {personSelector, def: {required, readonly}, name, def} = this.props;
        return <Field
            name={name}
            inputDef={def}
            personSelector={personSelector}
            JSXLabel={<Lang text={'E-mail'}/>}
            inputType={'text'}
            component={Input}
            providerInput={BaseInput}
            placeholder={'Name'}
            readonly={readonly}
            validate={required ? [requiredTest] : []}
        />;
    }
}
