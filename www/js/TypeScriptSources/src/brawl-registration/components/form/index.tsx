import * as React from 'react';
import { connect } from 'react-redux';
import {
    Field,
    Form,
    InjectedFormProps,
    reduxForm,
} from 'redux-form';
import Lang from '../../../lang/components/lang';
import { required } from '../../../person-provider/validation';
import { IStore } from '../../reducers';
import PersonsContainer from '../containers/persons';
import BaseInput from '../inputs/base-input';
import { asyncValidate } from './fields/team-name/validate';

class BrawlForm extends React.Component<InjectedFormProps, {}> {

    public render() {
        // const {valid, submitting, handleSubmit, onSubmit, tasks, teams} = this.props;
        const {handleSubmit} = this.props;
// handleSubmit(onSubmit)
        return (
            <Form onSubmit={handleSubmit((...args) => {
                console.log('submit');
            })}>
                <Field
                    validate={[required]}
                    name={'teamName'}
                    component={BaseInput}
                    JSXLabel={<Lang text={'Team name'}/>}
                />
                <PersonsContainer/>
                <button type='submit'>Submit</button>
            </Form>
        );
    }
}

export const FORM_NAME = 'brawlRegistrationForm';

const mapDispatchToProps = (): {} => {
    return {};
};

const mapStateToProps = (state: IStore): {} => {
    return {};

};

export default reduxForm({
    asyncChangeFields: ['teamName'],
    asyncValidate,
    form: FORM_NAME,
    // initialValues: {persons, teamName: "ahoj"},
    /* validate: () => {
         return {};
     },*/

})(connect(mapStateToProps, mapDispatchToProps)(BrawlForm));